<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Message\UpdateNodeTypeSchemaMessage;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\Forms\NodeTypeFieldType;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class NodeTypeFieldsController extends RozierApp
{
    public function __construct(
        private readonly bool $allowNodeTypeEdition,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws RuntimeError
     */
    public function listAction(Request $request, int $nodeTypeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        /** @var NodeType|null $nodeType */
        $nodeType = $this->em()->find(NodeType::class, $nodeTypeId);

        if (null === $nodeType) {
            throw new ResourceNotFoundException();
        }

        $fields = $nodeType->getFields();

        $this->assignation['nodeType'] = $nodeType;
        $this->assignation['fields'] = $fields;

        return $this->render('@RoadizRozier/node-type-fields/list.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $nodeTypeFieldId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        /** @var NodeTypeField|null $field */
        $field = $this->em()->find(NodeTypeField::class, $nodeTypeFieldId);

        if (null === $field) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['nodeType'] = $field->getNodeType();
        $this->assignation['field'] = $field;

        $form = $this->createForm(NodeTypeFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->allowNodeTypeEdition) {
                $form->addError(new FormError('You cannot edit node-type fields in production.'));
            } else {
                $this->em()->flush();

                /** @var NodeType $nodeType */
                $nodeType = $field->getNodeType();
                $this->messageBus->dispatch(new Envelope(new UpdateNodeTypeSchemaMessage($nodeType->getId())));

                $msg = $this->getTranslator()->trans('nodeTypeField.%name%.updated', ['%name%' => $field->getName()]);
                $this->publishConfirmMessage($request, $msg, $field);

                return $this->redirectToRoute(
                    'nodeTypeFieldsEditPage',
                    [
                        'nodeTypeFieldId' => $nodeTypeFieldId,
                    ]
                );
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/node-type-fields/edit.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function addAction(Request $request, int $nodeTypeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        $field = new NodeTypeField();
        /** @var NodeType|null $nodeType */
        $nodeType = $this->em()->find(NodeType::class, $nodeTypeId);

        if (null === $nodeType) {
            throw new ResourceNotFoundException();
        }

        $latestPosition = $this->em()
                               ->getRepository(NodeTypeField::class)
                               ->findLatestPositionInNodeType($nodeType);
        $field->setNodeType($nodeType);
        $field->setPosition($latestPosition + 1);
        $field->setType(NodeTypeField::STRING_T);

        $this->assignation['nodeType'] = $nodeType;
        $this->assignation['field'] = $field;

        $form = $this->createForm(NodeTypeFieldType::class, $field, [
            'disabled' => !$this->allowNodeTypeEdition,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->allowNodeTypeEdition) {
                $form->addError(new FormError('You cannot add node-type fields in production.'));
            } else {
                try {
                    $this->em()->persist($field);
                    $this->em()->flush();
                    $this->em()->refresh($nodeType);

                    $this->messageBus->dispatch(new Envelope(new UpdateNodeTypeSchemaMessage($nodeType->getId())));

                    $msg = $this->getTranslator()->trans(
                        'nodeTypeField.%name%.created',
                        ['%name%' => $field->getName()]
                    );
                    $this->publishConfirmMessage($request, $msg, $field);

                    return $this->redirectToRoute(
                        'nodeTypeFieldsListPage',
                        [
                            'nodeTypeId' => $nodeTypeId,
                        ]
                    );
                } catch (\Exception $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/node-type-fields/add.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $nodeTypeFieldId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODEFIELDS_DELETE');

        /** @var NodeTypeField|null $field */
        $field = $this->em()->find(NodeTypeField::class, $nodeTypeFieldId);

        if (null === $field) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->allowNodeTypeEdition) {
                $form->addError(new FormError('You cannot delete node-type fields in production.'));
            } else {
                /** @var NodeType $nodeType */
                $nodeType = $field->getNodeType();
                $nodeTypeId = $nodeType->getId();
                $this->em()->remove($field);
                $this->em()->flush();

                $this->messageBus->dispatch(new Envelope(new UpdateNodeTypeSchemaMessage($nodeTypeId)));

                $msg = $this->getTranslator()->trans(
                    'nodeTypeField.%name%.deleted',
                    ['%name%' => $field->getName()]
                );
                $this->publishConfirmMessage($request, $msg, $field);

                return $this->redirectToRoute(
                    'nodeTypeFieldsListPage',
                    [
                        'nodeTypeId' => $nodeTypeId,
                    ]
                );
            }
        }

        $this->assignation['field'] = $field;
        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/node-type-fields/delete.html.twig', $this->assignation);
    }
}
