<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\RozierBundle\Form\SettingGroupType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SettingGroupController extends AbstractAdminController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof SettingGroup;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'settingGroup';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new SettingGroup();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/settingGroups';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_SETTINGS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return SettingGroup::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return SettingGroupType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'settingGroupsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'settingGroupsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof SettingGroup) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['name' => 'ASC'];
    }

    public function deleteAction(Request $request, int|string $id): ?Response
    {
        /** @var SettingGroup|null $item */
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
            'delete.settingGroup.%name%',
            ['%name%' => $this->getEntityName($item)]
        );

        return $this->render('@RoadizRozier/admin/delete.html.twig', [
            'title' => $title,
            'headPath' => '@RoadizRozier/settingGroups/head.html.twig',
            'cancelPath' => $this->generateUrl('settingGroupsHomePage'),
            'alertMessage' => 'are_you_sure.delete.settingGroup',
            'form' => $form->createView(),
        ]);
    }
}
