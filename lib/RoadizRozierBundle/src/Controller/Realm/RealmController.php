<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Realm;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Form\RealmType;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\RozierBundle\Controller\AbstractAdminWithBulkController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RealmController extends AbstractAdminWithBulkController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof RealmInterface;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'realms';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Realm();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/realms';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_REALMS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return Realm::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return RealmType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'realmsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'realmsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        return $item instanceof RealmInterface ? $item->getName() : '';
    }

    public function deleteAction(Request $request, int|string $id): ?Response
    {
        /** @var Realm|null $item */
        $item = $this->getRepository()->find($id);

        if (!($item instanceof PersistableInterface)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted($this->getRequiredDeletionRole(), $item);
        $this->additionalAssignation($request);

        $this->prepareWorkingItem($item);
        $this->denyAccessUnlessItemGranted($item);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->managerRegistry->getManagerForClass($this->getEntityClass());
            /*
             * Events are dispatched before entity manager is flushed
             * to be able to throw exceptions before it is persisted.
             */
            $event = $this->createDeleteEvent($item);
            $this->dispatchSingleOrMultipleEvent($event);
            $entityManager?->remove($item);
            $entityManager?->flush();

            $postEvent = $this->createPostDeleteEvent($item);
            $this->dispatchSingleOrMultipleEvent($postEvent);

            $msg = $this->translator->trans(
                '%namespace%.%item%.was_deleted',
                [
                    '%item%' => $this->getEntityName($item),
                    '%namespace%' => $this->translator->trans($this->getNamespace()),
                ]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $item);

            return $this->getPostDeleteResponse($item);
        }

        $title = $this->translator->trans(
            'delete.realm.%name%',
            ['%name%' => $this->getEntityName($item)]
        );

        return $this->render('@RoadizRozier/admin/delete.html.twig', [
            'title' => $title,
            'headPath' => '@RoadizRozier/realms/head.html.twig',
            'cancelPath' => $this->generateUrl('realmsHomePage'),
            'alertMessage' => 'are_you_sure.delete.realm',
            'form' => $form->createView(),
        ]);
    }
}
