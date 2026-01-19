<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\AttributeValueTranslation;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Form\AttributeValueTranslationType;
use RZ\Roadiz\CoreBundle\Form\AttributeValueType;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class NodeAttributeController extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly FormErrorSerializer $formErrorSerializer,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
        private readonly NodeTypes $nodeTypesBag,
        private readonly AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
        private readonly TranslationRepository $translationRepository,
    ) {
    }

    private function getNodesSources(Node $node, Translation $translation): NodesSources
    {
        /** @var NodesSources|null $nodeSource */
        $nodeSource = $this->allStatusesNodesSourcesRepository->findOneBy(['translation' => $translation, 'node' => $node]);

        if (null === $nodeSource) {
            throw $this->createNotFoundException('Node-source does not exist');
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_ATTRIBUTE, $node);

        return $nodeSource;
    }

    #[Route(
        path: '/rz-admin/nodes/edit/{nodeId}/source/{translationId}/attributes',
        name: 'nodesEditAttributesPage',
        requirements: [
            'nodeId' => '[0-9]+',
            'translationId' => '[0-9]+',
        ],
    )]
    public function editAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
        #[MapEntity(
            expr: 'repository.find(translationId)',
            message: 'Translation does not exist'
        )]
        Translation $translation,
    ): Response {
        $nodeSource = $this->getNodesSources($node, $translation);

        if (!$this->isAttributable($node)) {
            throw $this->createNotFoundException('Node type is not attributable');
        }

        $assignation = [];

        if (null !== $response = $this->handleAddAttributeForm($request, $node, $translation, $assignation)) {
            return $response;
        }

        $isJson =
            $request->isXmlHttpRequest()
            || 'json' === $request->getRequestFormat('html')
            || \in_array(
                'application/json',
                $request->getAcceptableContentTypes()
            );

        $assignation['attribute_value_translation_forms'] = [];
        $nodeType = $this->nodeTypesBag->get($node->getNodeTypeName());
        if (!$nodeType instanceof NodeType) {
            throw new \RuntimeException('Cannot create node from invalid NodeType.');
        }
        $orderByWeight = $nodeType->isSortingAttributesByWeight();
        $attributeValues = $this->managerRegistry->getRepository(AttributeValue::class)->findByAttributable(
            $node,
            $orderByWeight
        );
        /** @var AttributeValue $attributeValue */
        foreach ($attributeValues as $attributeValue) {
            $name = $node->getNodeName().'_attribute_'.$attributeValue->getId();
            $attributeValueTranslation = $attributeValue->getAttributeValueTranslation($translation);
            if (null === $attributeValueTranslation) {
                $attributeValueTranslation = new AttributeValueTranslation();
                $attributeValueTranslation->setAttributeValue($attributeValue);
                $attributeValueTranslation->setTranslation($translation);
                $this->managerRegistry->getManager()->persist($attributeValueTranslation);
            }
            $attributeValueTranslationForm = $this->formFactory->createNamedBuilder(
                $name,
                AttributeValueTranslationType::class,
                $attributeValueTranslation
            )->getForm();
            $attributeValueTranslationForm->handleRequest($request);

            if ($attributeValueTranslationForm->isSubmitted()) {
                if ($attributeValueTranslationForm->isValid()) {
                    $this->managerRegistry->getManager()->flush();

                    $this->eventDispatcher->dispatch(new NodesSourcesUpdatedEvent($nodeSource));

                    $msg = $this->translator->trans(
                        'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                        [
                            '%name%' => $attributeValue->getAttribute()?->getLabelOrCode($translation),
                            '%nodeName%' => $nodeSource->getTitle(),
                        ]
                    );
                    $this->logTrail->publishConfirmMessage($request, $msg, $nodeSource);

                    if ($isJson) {
                        return new JsonResponse([
                            'status' => 'success',
                            'message' => $msg,
                        ], Response::HTTP_ACCEPTED);
                    }

                    return $this->redirectToRoute('nodesEditAttributesPage', [
                        'nodeId' => $node->getId(),
                        'translationId' => $translation->getId(),
                    ]);
                } else {
                    $errors = $this->formErrorSerializer->getErrorsAsArray($attributeValueTranslationForm);
                    /*
                     * Handle errors when Ajax POST requests
                     */
                    if ($isJson) {
                        return new JsonResponse([
                            'status' => 'fail',
                            'errors' => $errors,
                            'message' => $this->translator->trans('form_has_errors.check_you_fields'),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    foreach ($errors as $error) {
                        $this->logTrail->publishErrorMessage($request, $error);
                    }
                }
            }

            $assignation['attribute_value_translation_forms'][] = $attributeValueTranslationForm->createView();
        }

        $availableTranslations = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findAvailableTranslationsForNode($node);

        return $this->render('@RoadizRozier/nodes/attributes/edit.html.twig', [
            ...$assignation,
            'source' => $nodeSource,
            'translation' => $translation,
            'node' => $node,
            'order_by_weight' => $orderByWeight,
            'available_translations' => $availableTranslations,
        ]);
    }

    protected function hasAttributes(): bool
    {
        return $this->managerRegistry->getRepository(Attribute::class)->countBy([]) > 0;
    }

    protected function isAttributable(Node $node): bool
    {
        $nodeType = $this->nodeTypesBag->get($node->getNodeTypeName());
        if ($nodeType instanceof NodeType) {
            return $nodeType->isAttributable();
        }

        return false;
    }

    protected function handleAddAttributeForm(
        Request $request,
        Node $node,
        Translation $translation,
        array &$assignation,
    ): ?RedirectResponse {
        if (!$this->isAttributable($node)) {
            return null;
        }
        if (!$this->hasAttributes()) {
            return null;
        }
        $attributeValue = new AttributeValue();
        $attributeValue->setAttributable($node);
        $addAttributeForm = $this->createForm(AttributeValueType::class, $attributeValue, [
            'translation' => $this->translationRepository->findDefault(),
        ]);
        $addAttributeForm->handleRequest($request);

        if ($addAttributeForm->isSubmitted() && $addAttributeForm->isValid()) {
            $this->managerRegistry->getManager()->persist($attributeValue);
            $this->managerRegistry->getManager()->flush();

            $nodeSource = $node->getNodeSourcesByTranslation($translation)->first() ?: null;
            if ($nodeSource instanceof NodesSources) {
                $msg = $this->translator->trans(
                    'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                    [
                        '%name%' => $attributeValue->getAttribute()?->getLabelOrCode($translation),
                        '%nodeName%' => $nodeSource->getTitle(),
                    ]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $nodeSource);
            }

            return $this->redirectToRoute('nodesEditAttributesPage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }
        $assignation['addAttributeForm'] = $addAttributeForm->createView();

        return null;
    }

    #[Route(
        path: '/rz-admin/nodes/edit/{nodeId}/source/{translationId}/attributes/{attributeValueId}/delete',
        name: 'nodesDeleteAttributesPage',
        requirements: [
            'nodeId' => '[0-9]+',
            'translationId' => '[0-9]+',
            'attributeValueId' => '[0-9]+',
        ],
    )]
    public function deleteAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
        #[MapEntity(
            expr: 'repository.find(translationId)',
            message: 'Translation does not exist'
        )]
        Translation $translation,
        #[MapEntity(
            expr: 'repository.find(attributeValueId)',
            message: 'AttributeValue does not exist'
        )]
        AttributeValue $attributeValue,
    ): Response {
        $nodeSource = $this->getNodesSources($node, $translation);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->managerRegistry->getManager()->remove($attributeValue);
                $this->managerRegistry->getManager()->flush();

                $msg = $this->translator->trans(
                    'attribute.%name%.deleted_from_node.%nodeName%',
                    [
                        '%name%' => $attributeValue->getAttribute()?->getLabelOrCode($translation),
                        '%nodeName%' => $nodeSource->getTitle(),
                    ]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $attributeValue);
            } catch (\RuntimeException $e) {
                $this->logTrail->publishErrorMessage($request, $e->getMessage(), $attributeValue);
            }

            return $this->redirectToRoute('nodesEditAttributesPage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

        return $this->render('@RoadizRozier/nodes/attributes/delete.html.twig', [
            'item' => $attributeValue,
            'source' => $nodeSource,
            'translation' => $translation,
            'node' => $node,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/rz-admin/nodes/edit/{nodeId}/source/{translationId}/attributes/{attributeValueId}/reset',
        name: 'nodesResetAttributesPage',
        requirements: [
            'nodeId' => '[0-9]+',
            'translationId' => '[0-9]+',
            'attributeValueId' => '[0-9]+',
        ],
    )]
    public function resetAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(nodeId)',
            message: 'Node does not exist'
        )]
        Node $node,
        #[MapEntity(
            expr: 'repository.find(translationId)',
            message: 'Translation does not exist'
        )]
        Translation $translation,
        #[MapEntity(
            expr: 'repository.find(attributeValueId)',
            message: 'AttributeValue does not exist'
        )]
        AttributeValue $attributeValue,
    ): Response {
        /** @var AttributeValueTranslation|null $attributeValueTranslation */
        $attributeValueTranslation = $this->managerRegistry
            ->getRepository(AttributeValueTranslation::class)
            ->findOneBy([
                'attributeValue' => $attributeValue,
                'translation' => $translation,
            ]);
        if (null === $attributeValueTranslation) {
            throw $this->createNotFoundException('AttributeValueTranslation does not exist.');
        }

        $nodeSource = $this->getNodesSources($node, $translation);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->managerRegistry->getManager()->remove($attributeValueTranslation);
                $this->managerRegistry->getManager()->flush();

                $msg = $this->translator->trans(
                    'attribute.%name%.reset_for_node.%nodeName%',
                    [
                        '%name%' => $attributeValueTranslation->getAttribute()->getLabelOrCode($translation),
                        '%nodeName%' => $nodeSource->getTitle(),
                    ]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $attributeValueTranslation);
            } catch (\RuntimeException $e) {
                $this->logTrail->publishErrorMessage($request, $e->getMessage(), $attributeValueTranslation);
            }

            return $this->redirectToRoute('nodesEditAttributesPage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

        return $this->render('@RoadizRozier/nodes/attributes/reset.html.twig', [
            'item' => $attributeValueTranslation,
            'source' => $nodeSource,
            'translation' => $translation,
            'node' => $node,
            'form' => $form->createView(),
        ]);
    }
}
