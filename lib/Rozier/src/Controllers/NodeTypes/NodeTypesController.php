<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\NodeTypes;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Message\DeleteNodeTypeMessage;
use RZ\Roadiz\CoreBundle\Message\UpdateNodeTypeSchemaMessage;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Themes\Rozier\Forms\NodeTypeType;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class NodeTypesController extends RozierApp
{
    public function __construct(
        private readonly bool $allowNodeTypeEdition,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');
        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            NodeType::class,
            [],
            ['name' => 'ASC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);

        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('node_types_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);

        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['node_types'] = $listManager->getEntities();

        return $this->render('@RoadizRozier/node-types/list.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param int $nodeTypeId
     * @return Response
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $nodeTypeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');

        /** @var NodeType|null $nodeType */
        $nodeType = $this->em()->find(NodeType::class, $nodeTypeId);

        if (!($nodeType instanceof NodeType)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(NodeTypeType::class, $nodeType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em()->flush();
                $this->messageBus->dispatch(new Envelope(new UpdateNodeTypeSchemaMessage($nodeType->getId())));

                $msg = $this->getTranslator()->trans('nodeType.%name%.updated', ['%name%' => $nodeType->getName()]);
                $this->publishConfirmMessage($request, $msg, $nodeType);

                return $this->redirectToRoute('nodeTypesEditPage', [
                    'nodeTypeId' => $nodeTypeId
                ]);
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['nodeType'] = $nodeType;

        return $this->render('@RoadizRozier/node-types/edit.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     */
    public function addAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES');
        $nodeType = new NodeType();

        $form = $this->createForm(NodeTypeType::class, $nodeType, [
            'disabled' => !$this->allowNodeTypeEdition
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->allowNodeTypeEdition) {
                $form->addError(new FormError('You cannot create a node-type in production mode.'));
            } else {
                try {
                    $this->em()->persist($nodeType);
                    $this->em()->flush();

                    $this->messageBus->dispatch(new Envelope(new UpdateNodeTypeSchemaMessage($nodeType->getId())));

                    $msg = $this->getTranslator()->trans('nodeType.%name%.created', ['%name%' => $nodeType->getName()]);
                    $this->publishConfirmMessage($request, $msg, $nodeType);

                    return $this->redirectToRoute('nodeTypesEditPage', [
                        'nodeTypeId' => $nodeType->getId()
                    ]);
                } catch (EntityAlreadyExistsException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['nodeType'] = $nodeType;

        return $this->render('@RoadizRozier/node-types/add.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param int $nodeTypeId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $nodeTypeId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_NODETYPES_DELETE');

        /** @var NodeType $nodeType */
        $nodeType = $this->em()->find(NodeType::class, $nodeTypeId);

        if (!($nodeType instanceof NodeType)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->allowNodeTypeEdition) {
                $form->addError(new FormError('You cannot delete a node-type in production mode.'));
            } else {
                $this->messageBus->dispatch(new Envelope(new DeleteNodeTypeMessage($nodeType->getId())));

                $msg = $this->getTranslator()->trans('nodeType.%name%.deleted', ['%name%' => $nodeType->getName()]);
                $this->publishConfirmMessage($request, $msg, $nodeType);

                return $this->redirectToRoute('nodeTypesHomePage');
            }
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['nodeType'] = $nodeType;

        return $this->render('@RoadizRozier/node-types/delete.html.twig', $this->assignation);
    }
}
