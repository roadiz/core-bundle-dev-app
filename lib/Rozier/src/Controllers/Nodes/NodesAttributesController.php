<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\AttributeValueTranslation;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Form\AttributeValueTranslationType;
use RZ\Roadiz\CoreBundle\Form\AttributeValueType;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class NodesAttributesController extends RozierApp
{
    private FormFactoryInterface $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param Request $request
     * @param int $nodeId
     * @param int $translationId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $nodeId, int $translationId): Response
    {
        /** @var Translation|null $translation */
        $translation = $this->em()->find(Translation::class, $translationId);
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);

        if (null === $translation || null === $node) {
            throw $this->createNotFoundException('Node-source does not exist');
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_ATTRIBUTE, $node);

        /** @var NodesSources|null $nodeSource */
        $nodeSource = $this->em()
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->findOneBy(['translation' => $translation, 'node' => $node]);

        if (null === $nodeSource) {
            throw $this->createNotFoundException('Node-source does not exist');
        }

        if (null !== $response = $this->handleAddAttributeForm($request, $node, $translation)) {
            return $response;
        }

        $isJson =
            $request->isXmlHttpRequest() ||
            $request->getRequestFormat('html') === 'json' ||
            \in_array(
                'application/json',
                $request->getAcceptableContentTypes()
            );

        $this->assignation['attribute_value_translation_forms'] = [];
        $attributeValues = $node->getAttributeValues();
        /** @var AttributeValue $attributeValue */
        foreach ($attributeValues as $attributeValue) {
            $name = $node->getNodeName() . '_attribute_' . $attributeValue->getId();
            $attributeValueTranslation = $attributeValue->getAttributeValueTranslation($translation);
            if (null === $attributeValueTranslation) {
                $attributeValueTranslation = new AttributeValueTranslation();
                $attributeValueTranslation->setAttributeValue($attributeValue);
                $attributeValueTranslation->setTranslation($translation);
                $this->em()->persist($attributeValueTranslation);
            }
            $attributeValueTranslationForm = $this->formFactory->createNamedBuilder(
                $name,
                AttributeValueTranslationType::class,
                $attributeValueTranslation
            )->getForm();
            $attributeValueTranslationForm->handleRequest($request);

            if ($attributeValueTranslationForm->isSubmitted()) {
                if ($attributeValueTranslationForm->isValid()) {
                    $this->em()->flush();

                    /*
                     * Dispatch event
                     */
                    $this->dispatchEvent(new NodesSourcesUpdatedEvent($nodeSource));

                    $msg = $this->getTranslator()->trans(
                        'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                        [
                            '%name%' => $attributeValue->getAttribute()->getLabelOrCode($translation),
                            '%nodeName%' => $nodeSource->getTitle(),
                        ]
                    );
                    $this->publishConfirmMessage($request, $msg, $nodeSource);

                    if ($isJson) {
                        return new JsonResponse([
                            'status' => 'success',
                            'message' => $msg,
                        ], JsonResponse::HTTP_ACCEPTED);
                    }
                    return $this->redirectToRoute('nodesEditAttributesPage', [
                        'nodeId' => $node->getId(),
                        'translationId' => $translation->getId(),
                    ]);
                } else {
                    $errors = $this->getErrorsAsArray($attributeValueTranslationForm);
                    /*
                     * Handle errors when Ajax POST requests
                     */
                    if ($isJson) {
                        return new JsonResponse([
                            'status' => 'fail',
                            'errors' => $errors,
                            'message' => $this->getTranslator()->trans('form_has_errors.check_you_fields'),
                        ], JsonResponse::HTTP_BAD_REQUEST);
                    }
                    foreach ($errors as $error) {
                        $this->publishErrorMessage($request, $error);
                    }
                }
            }

            $this->assignation['attribute_value_translation_forms'][] = $attributeValueTranslationForm->createView();
        }

        $this->assignation['source'] = $nodeSource;
        $this->assignation['translation'] = $translation;
        $availableTranslations = $this->em()
            ->getRepository(Translation::class)
            ->findAvailableTranslationsForNode($node);
        $this->assignation['available_translations'] = $availableTranslations;
        $this->assignation['node'] = $node;

        return $this->render('@RoadizRozier/nodes/attributes/edit.html.twig', $this->assignation);
    }

    protected function hasAttributes(): bool
    {
        return $this->em()->getRepository(Attribute::class)->countBy([]) > 0;
    }

    /**
     * @param Request     $request
     * @param Node        $node
     * @param Translation $translation
     *
     * @return RedirectResponse|null
     */
    protected function handleAddAttributeForm(Request $request, Node $node, Translation $translation): ?RedirectResponse
    {
        if (!$this->hasAttributes()) {
            return null;
        }
        $attributeValue = new AttributeValue();
        $attributeValue->setAttributable($node);
        $addAttributeForm = $this->createForm(AttributeValueType::class, $attributeValue, [
            'entityManager' => $this->em(),
            'translation' => $this->em()->getRepository(Translation::class)->findDefault(),
        ]);
        $addAttributeForm->handleRequest($request);

        if ($addAttributeForm->isSubmitted() && $addAttributeForm->isValid()) {
            $this->em()->persist($attributeValue);
            $this->em()->flush();

            return $this->redirectToRoute('nodesEditAttributesPage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }
        $this->assignation['addAttributeForm'] = $addAttributeForm->createView();

        return null;
    }

    /**
     * @param Request $request
     * @param int $nodeId
     * @param int $translationId
     * @param int $attributeValueId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $nodeId, int $translationId, int $attributeValueId): Response
    {
        /** @var AttributeValue|null $item */
        $item = $this->em()->find(AttributeValue::class, $attributeValueId);
        if ($item === null) {
            throw $this->createNotFoundException('AttributeValue does not exist.');
        }
        /** @var Translation|null $translation */
        $translation = $this->em()->find(Translation::class, $translationId);
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);

        if (null === $translation || null === $node) {
            throw $this->createNotFoundException('Node-source does not exist');
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_ATTRIBUTE, $node);

        /** @var NodesSources|null $nodeSource */
        $nodeSource = $this->em()
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->findOneBy(['translation' => $translation, 'node' => $node]);

        if (null === $nodeSource) {
            throw $this->createNotFoundException('Node-source does not exist');
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em()->remove($item);
                $this->em()->flush();

                $msg = $this->getTranslator()->trans(
                    'attribute.%name%.deleted_from_node.%nodeName%',
                    [
                        '%name%' => $item->getAttribute()->getLabelOrCode($translation),
                        '%nodeName%' => $nodeSource->getTitle(),
                    ]
                );
                $this->publishConfirmMessage($request, $msg, $item);
            } catch (\RuntimeException $e) {
                $this->publishErrorMessage($request, $e->getMessage(), $item);
            }

            return $this->redirectToRoute('nodesEditAttributesPage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;
        $this->assignation['source'] = $nodeSource;
        $this->assignation['translation'] = $translation;
        $this->assignation['node'] = $node;

        return $this->render('@RoadizRozier/nodes/attributes/delete.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param int $nodeId
     * @param int $translationId
     * @param int $attributeValueId
     * @return Response
     * @throws RuntimeError
     */
    public function resetAction(Request $request, int $nodeId, int $translationId, int $attributeValueId): Response
    {
        /** @var AttributeValueTranslation|null $item */
        $item = $this->em()
            ->getRepository(AttributeValueTranslation::class)
            ->findOneBy([
                'attributeValue' => $attributeValueId,
                'translation' => $translationId
            ]);
        if ($item === null) {
            throw $this->createNotFoundException('AttributeValueTranslation does not exist.');
        }
        /** @var Translation|null $translation */
        $translation = $this->em()->find(Translation::class, $translationId);
        /** @var Node|null $node */
        $node = $this->em()->find(Node::class, $nodeId);

        if (null === $translation || null === $node) {
            throw $this->createNotFoundException('Node-source does not exist');
        }

        $this->denyAccessUnlessGranted(NodeVoter::EDIT_ATTRIBUTE, $node);

        /** @var NodesSources|null $nodeSource */
        $nodeSource = $this->em()
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->findOneBy(['translation' => $translation, 'node' => $node]);

        if (null === $nodeSource) {
            throw $this->createNotFoundException('Node-source does not exist');
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em()->remove($item);
                $this->em()->flush();

                $msg = $this->getTranslator()->trans(
                    'attribute.%name%.reset_for_node.%nodeName%',
                    [
                        '%name%' => $item->getAttribute()->getLabelOrCode($translation),
                        '%nodeName%' => $nodeSource->getTitle(),
                    ]
                );
                $this->publishConfirmMessage($request, $msg, $item);
            } catch (\RuntimeException $e) {
                $this->publishErrorMessage($request, $e->getMessage(), $item);
            }

            return $this->redirectToRoute('nodesEditAttributesPage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;
        $this->assignation['source'] = $nodeSource;
        $this->assignation['translation'] = $translation;
        $this->assignation['node'] = $node;

        return $this->render('@RoadizRozier/nodes/attributes/reset.html.twig', $this->assignation);
    }
}
