<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Nodes;

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
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly FormErrorSerializer $formErrorSerializer,
        private readonly NodeTypes $nodeTypesBag,
    ) {
    }

    /**
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

        if (!$this->isAttributable($node)) {
            throw $this->createNotFoundException('Node type is not attributable');
        }

        if (null !== $response = $this->handleAddAttributeForm($request, $node, $translation)) {
            return $response;
        }

        $isJson =
            $request->isXmlHttpRequest()
            || 'json' === $request->getRequestFormat('html')
            || \in_array(
                'application/json',
                $request->getAcceptableContentTypes()
            );

        $this->assignation['attribute_value_translation_forms'] = [];
        $nodeType = $this->nodeTypesBag->get($node->getNodeTypeName());
        if (!$nodeType instanceof NodeType) {
            throw new \RuntimeException('Cannot create node from invalid NodeType.');
        }
        $orderByWeight = $nodeType->isSortingAttributesByWeight();
        $attributeValues = $this->em()->getRepository(AttributeValue::class)->findByAttributable(
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
                            'message' => $this->getTranslator()->trans('form_has_errors.check_you_fields'),
                        ], Response::HTTP_BAD_REQUEST);
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
        $this->assignation['order_by_weight'] = $orderByWeight;
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

    protected function isAttributable(Node $node): bool
    {
        $nodeType = $this->nodeTypesBag->get($node->getNodeTypeName());
        if ($nodeType instanceof NodeType) {
            return $nodeType->isAttributable();
        }

        return false;
    }

    protected function handleAddAttributeForm(Request $request, Node $node, Translation $translation): ?RedirectResponse
    {
        if (!$this->isAttributable($node)) {
            return null;
        }
        if (!$this->hasAttributes()) {
            return null;
        }
        $attributeValue = new AttributeValue();
        $attributeValue->setAttributable($node);
        $addAttributeForm = $this->createForm(AttributeValueType::class, $attributeValue, [
            'translation' => $this->em()->getRepository(Translation::class)->findDefault(),
        ]);
        $addAttributeForm->handleRequest($request);

        if ($addAttributeForm->isSubmitted() && $addAttributeForm->isValid()) {
            $this->em()->persist($attributeValue);
            $this->em()->flush();

            $nodeSource = $node->getNodeSourcesByTranslation($translation)->first() ?: null;
            if ($nodeSource instanceof NodesSources) {
                $msg = $this->getTranslator()->trans(
                    'attribute_value_translation.%name%.updated_from_node.%nodeName%',
                    [
                        '%name%' => $attributeValue->getAttribute()->getLabelOrCode($translation),
                        '%nodeName%' => $nodeSource->getTitle(),
                    ]
                );
                $this->publishConfirmMessage($request, $msg, $nodeSource);
            }

            return $this->redirectToRoute('nodesEditAttributesPage', [
                'nodeId' => $node->getId(),
                'translationId' => $translation->getId(),
            ]);
        }
        $this->assignation['addAttributeForm'] = $addAttributeForm->createView();

        return null;
    }

    /**
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $nodeId, int $translationId, int $attributeValueId): Response
    {
        /** @var AttributeValue|null $item */
        $item = $this->em()->find(AttributeValue::class, $attributeValueId);
        if (null === $item) {
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
     * @throws RuntimeError
     */
    public function resetAction(Request $request, int $nodeId, int $translationId, int $attributeValueId): Response
    {
        /** @var AttributeValueTranslation|null $item */
        $item = $this->em()
            ->getRepository(AttributeValueTranslation::class)
            ->findOneBy([
                'attributeValue' => $attributeValueId,
                'translation' => $translationId,
            ]);
        if (null === $item) {
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
