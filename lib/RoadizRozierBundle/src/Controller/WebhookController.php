<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Webhook;
use RZ\Roadiz\CoreBundle\Form\WebhookType;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\CoreBundle\Webhook\Exception\TooManyWebhookTriggeredException;
use RZ\Roadiz\CoreBundle\Webhook\WebhookDispatcher;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class WebhookController extends AbstractAdminWithBulkController
{
    public function __construct(
        private readonly WebhookDispatcher $webhookDispatcher,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        LogTrail $logTrail,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($formFactory, $urlGenerator, $entityListManagerFactory, $managerRegistry, $translator, $logTrail, $eventDispatcher);
    }

    public function triggerAction(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        /** @var Webhook|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);

        if (!$item instanceof PersistableInterface) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessItemGranted($item);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->webhookDispatcher->dispatch($item);
                $this->em()->flush();

                $msg = $this->translator->trans(
                    'webhook.%item%.will_be_triggered_in.%seconds%',
                    [
                        '%item%' => $this->getEntityName($item),
                        '%seconds%' => $item->getThrottleSeconds(),
                    ]
                );
                $this->logTrail->publishConfirmMessage($request, $msg, $item);

                return $this->redirect($this->urlGenerator->generate(
                    $this->getDefaultRouteName(),
                    $this->getDefaultRouteParameters()
                ));
            } catch (TooManyWebhookTriggeredException $e) {
                $form->addError(new FormError('webhook.too_many_triggered_in_period', null, [
                    '%time%' => $e->getDoNotTriggerBefore()->format('H:i:s'),
                ], null, $e));
            }
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder().'/trigger.html.twig',
            $this->assignation,
        );
    }

    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Webhook;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'webhook';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Webhook();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/admin/webhooks';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_WEBHOOKS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return Webhook::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return WebhookType::class;
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'webhooksHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'webhooksEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Webhook) {
            return (string) $item;
        }

        return '';
    }

    #[\Override]
    protected function getBulkDeleteRouteName(): string
    {
        return 'webhooksBulkDeletePage';
    }
}
