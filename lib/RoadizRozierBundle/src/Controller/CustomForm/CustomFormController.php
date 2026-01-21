<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\CustomForm;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\RozierBundle\Controller\AbstractAdminWithBulkController;
use RZ\Roadiz\RozierBundle\Form\CustomFormType;
use RZ\Roadiz\RozierBundle\Form\CustomFormWebhookType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CustomFormController extends AbstractAdminWithBulkController
{
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof CustomForm;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'custom-form';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new CustomForm();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/custom-forms';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_CUSTOMFORMS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return CustomForm::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return CustomFormType::class;
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['createdAt' => 'DESC'];
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'customFormsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'customFormsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof CustomForm) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
    protected function getBulkDeleteRouteName(): ?string
    {
        return 'customFormsBulkDeletePage';
    }

    /**
     * Configure webhook settings for a custom form.
     */
    #[Route(
        path: '/rz-admin/custom-forms/webhook/{customForm}',
        name: 'customFormsEditWebhookPage',
        requirements: ['customForm' => '\d+'],
        methods: ['GET', 'POST'],
    )]
    public function webhookAction(Request $request, CustomForm $customForm): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS_WEBHOOKS');

        $form = $this->createForm(CustomFormWebhookType::class, $customForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->managerRegistry->getManagerForClass(CustomForm::class);

            if (null === $entityManager) {
                throw new \RuntimeException(sprintf('No EntityManager found for class %s.', CustomForm::class));
            }
            /*
             * Events are dispatched before entity manager is flushed
             * to be able to throw exceptions before it is persisted.
             */
            $event = $this->createUpdateEvent($customForm);
            $this->dispatchSingleOrMultipleEvent($event);
            $entityManager->flush();

            /*
             * Event that requires that EM is flushed
             */
            $postEvent = $this->createPostUpdateEvent($customForm);
            $this->dispatchSingleOrMultipleEvent($postEvent);

            $msg = $this->translator->trans(
                '%namespace%.%item%.was_updated',
                [
                    '%item%' => $this->getEntityName($customForm),
                    '%namespace%' => $this->translator->trans($this->getNamespace()),
                ]
            );
            $this->logTrail->publishConfirmMessage($request, $msg, $customForm);

            return $this->redirect($this->urlGenerator->generate(
                'customFormsEditWebhookPage',
                ['customForm' => $customForm->getId()]
            ));
        }

        return $this->render(
            '@RoadizRozier/custom-forms/webhook.html.twig',
            [
                'form' => $form->createView(),
                'item' => $customForm,
            ],
        );
    }
}
