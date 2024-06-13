<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Webhook;
use RZ\Roadiz\CoreBundle\Form\WebhookType;
use RZ\Roadiz\CoreBundle\Webhook\Exception\TooManyWebhookTriggeredException;
use RZ\Roadiz\CoreBundle\Webhook\WebhookDispatcher;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class WebhookController extends AbstractAdminController
{
    private WebhookDispatcher $webhookDispatcher;

    public function __construct(
        WebhookDispatcher $webhookDispatcher,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($serializer, $urlGenerator);
        $this->webhookDispatcher = $webhookDispatcher;
    }

    public function triggerAction(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        /** @var Webhook|null $item */
        $item = $this->em()->find($this->getEntityClass(), $id);

        if (!($item instanceof PersistableInterface)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessItemGranted($item);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->webhookDispatcher->dispatch($item);
                $this->em()->flush();

                $msg = $this->getTranslator()->trans(
                    'webhook.%item%.will_be_triggered_in.%seconds%',
                    [
                        '%item%' => $this->getEntityName($item),
                        '%seconds%' => $item->getThrottleSeconds(),
                    ]
                );
                $this->publishConfirmMessage($request, $msg);

                return $this->redirect($this->urlGenerator->generate(
                    $this->getDefaultRouteName(),
                    $this->getDefaultRouteParameters()
                ));
            } catch (TooManyWebhookTriggeredException $e) {
                $form->addError(new FormError('webhook.too_many_triggered_in_period', null, [
                    '%time%' => $e->getDoNotTriggerBefore()->format('H:i:s')
                ], null, $e));
            }
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['item'] = $item;

        return $this->render(
            $this->getTemplateFolder() . '/trigger.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Webhook;
    }

    protected function getNamespace(): string
    {
        return 'webhook';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new Webhook();
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/admin/webhooks';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_WEBHOOKS';
    }

    protected function getEntityClass(): string
    {
        return Webhook::class;
    }

    protected function getFormType(): string
    {
        return WebhookType::class;
    }

    protected function getDefaultRouteName(): string
    {
        return 'webhooksHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'webhooksEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Webhook) {
            return (string) $item;
        }
        return '';
    }
}
