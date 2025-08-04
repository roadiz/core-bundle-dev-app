<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAdminWithBulkController extends AbstractAdminController
{
    public function __construct(
        protected readonly FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        LogTrail $logTrail,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($urlGenerator, $entityListManagerFactory, $managerRegistry, $translator, $logTrail, $eventDispatcher);
    }

    #[\Override]
    protected function additionalAssignation(Request $request): void
    {
        parent::additionalAssignation($request);

        $this->assignation['hasBulkActions'] = true;

        if (null !== $this->getBulkDeleteRouteName()) {
            $bulkDeleteForm = $this->createDeleteBulkForm(true);
            $this->assignation['bulkDeleteForm'] = $bulkDeleteForm->createView();
            $this->assignation['hasBulkActions'] = true;
        }

        if (null !== $this->getBulkPublishRouteName()) {
            $bulkPublishForm = $this->createPublishBulkForm(true);
            $this->assignation['bulkPublishForm'] = $bulkPublishForm->createView();
            $this->assignation['hasBulkActions'] = true;
        }

        if (null !== $this->getBulkUnpublishRouteName()) {
            $bulkUnpublishForm = $this->createUnpublishBulkForm(true);
            $this->assignation['bulkUnpublishForm'] = $bulkUnpublishForm->createView();
            $this->assignation['hasBulkActions'] = true;
        }
    }

    protected function setPublishedAt(PersistableInterface $item, ?\DateTimeInterface $dateTime): void
    {
    }

    protected function removeItem(PersistableInterface $item): void
    {
        $this->em()->remove($item);
    }

    protected function getBulkPublishRouteName(): ?string
    {
        return null;
    }

    protected function getBulkUnpublishRouteName(): ?string
    {
        return null;
    }

    protected function getBulkDeleteRouteName(): ?string
    {
        return null;
    }

    /**
     * @return array<int|string>
     */
    protected function parseFormBulkIds(?FormInterface $form): array
    {
        if (null === $form) {
            return [];
        }
        if (!$form->isSubmitted() || !$form->isValid()) {
            return [];
        }
        $json = $form->getData();
        if (is_string($json)) {
            $json = stripslashes(trim($json, '"'));
        } else {
            return [];
        }
        $ids = \json_decode($json, true);

        return \array_filter($ids, fn ($id) =>
            // Allow int or UUID identifiers
            is_numeric($id) || is_string($id));
    }

    /**
     * @param callable(string): FormInterface                     $createBulkFormWithIds
     * @param callable(PersistableInterface, FormInterface): void $alterItemCallable
     *
     * @throws \Twig\Error\RuntimeError
     */
    protected function bulkAction(
        Request $request,
        string $requiredRole,
        FormInterface $bulkForm,
        FormInterface $form,
        callable $createBulkFormWithIds,
        string $templatePath,
        string $confirmMessageTemplate,
        callable $alterItemCallable,
        string $bulkFormName,
    ): Response {
        $this->denyAccessUnlessGranted($requiredRole);
        $bulkForm->handleRequest($request);
        $form->handleRequest($request);

        if ($bulkForm->isSubmitted() && $bulkForm->isValid()) {
            $ids = $this->parseFormBulkIds($bulkForm->get('id'));
            if (count($ids) < 1) {
                $bulkForm->addError(new FormError('No item selected.'));
            } else {
                $items = $this->getRepository()->findBy([
                    'id' => $ids,
                ]);
                $formWithIds = $createBulkFormWithIds(\json_encode($ids, JSON_THROW_ON_ERROR));
                if (!$formWithIds instanceof FormInterface) {
                    throw new \RuntimeException('Invalid form returned.');
                }
                $this->assignation['items'] = $items;
                $this->assignation['filters'] = ['itemCount' => count($items)];
                $this->assignation['form'] = $formWithIds->createView();
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $ids = $this->parseFormBulkIds($form->get('id'));
            if (count($ids) < 1) {
                $form->addError(new FormError('No item selected.'));
            } else {
                /** @var PersistableInterface[] $items */
                $items = $this->getRepository()->findBy([
                    'id' => $ids,
                ]);
                foreach ($items as $item) {
                    if ($this->supports($item)) {
                        $alterItemCallable($item, $form);
                        $updateEvent = $this->createUpdateEvent($item);
                        if (null !== $updateEvent) {
                            $this->dispatchSingleOrMultipleEvent($updateEvent);
                        }
                        $msg = $this->translator->trans(
                            $confirmMessageTemplate,
                            [
                                '%item%' => $this->getEntityName($item),
                                '%namespace%' => $this->translator->trans($this->getNamespace()),
                            ]
                        );
                        $this->logTrail->publishConfirmMessage($request, $msg, $item);
                    }
                }
                $this->em()->flush();

                return $this->redirect($this->urlGenerator->generate(
                    $this->getDefaultRouteName(),
                    $this->getDefaultRouteParameters()
                ));
            }
        }

        $this->assignation[$bulkFormName] = $bulkForm->createView();

        return $this->render(
            $templatePath,
            $this->assignation
        );
    }

    public function bulkDeleteAction(Request $request): Response
    {
        $this->additionalAssignation($request);

        return $this->bulkAction(
            $request,
            $this->getRequiredDeletionRole(),
            $this->createDeleteBulkForm(true),
            $this->createDeleteBulkForm(),
            fn (string $ids) => $this->createDeleteBulkForm(false, [
                'id' => $ids,
            ]),
            $this->getTemplateFolder().'/bulk_delete.html.twig',
            '%namespace%.%item%.was_deleted',
            function (PersistableInterface $item) {
                $this->removeItem($item);
            },
            'bulkDeleteForm'
        );
    }

    public function bulkPublishAction(Request $request): Response
    {
        $this->additionalAssignation($request);

        return $this->bulkAction(
            $request,
            $this->getRequiredRole(),
            $this->createPublishBulkForm(true),
            $this->createPublishBulkForm(),
            fn (string $ids) => $this->createPublishBulkForm(false, [
                'id' => $ids,
            ]),
            $this->getTemplateFolder().'/bulk_publish.html.twig',
            '%namespace%.%item%.was_published',
            function (PersistableInterface $item) {
                $this->setPublishedAt($item, new \DateTime('now'));
            },
            'bulkPublishForm'
        );
    }

    public function bulkUnpublishAction(Request $request): Response
    {
        $this->additionalAssignation($request);

        return $this->bulkAction(
            $request,
            $this->getRequiredRole(),
            $this->createUnpublishBulkForm(true),
            $this->createUnpublishBulkForm(),
            fn (string $ids) => $this->createUnpublishBulkForm(false, [
                'id' => $ids,
            ]),
            $this->getTemplateFolder().'/bulk_unpublish.html.twig',
            '%namespace%.%item%.was_unpublished',
            function (PersistableInterface $item) {
                $this->setPublishedAt($item, null);
            },
            'bulkUnpublishForm'
        );
    }

    protected function createBulkForm(
        ?string $routeName,
        string $formName,
        bool $get = false,
        ?array $data = null,
    ): FormInterface {
        if (null === $routeName) {
            throw new \RuntimeException('Bulk delete route name is not defined.');
        }

        if ($get) {
            $options = [
                'action' => $this->generateUrl($routeName),
                'method' => 'GET',
            ];
        } else {
            $options = [
                'action' => $this->generateUrl($routeName),
                'method' => 'POST',
            ];
        }

        return $this->formFactory->createNamedBuilder($formName, FormType::class, $data, $options)
            ->add('id', HiddenType::class, [
                'attr' => [
                    'class' => 'bulk-form-value',
                ],
            ])->getForm();
    }

    protected function createDeleteBulkForm(bool $get = false, ?array $data = null): FormInterface
    {
        return $this->createBulkForm(
            $this->getBulkDeleteRouteName(),
            'bulk-delete',
            $get,
            $data
        );
    }

    protected function createPublishBulkForm(bool $get = false, ?array $data = null): FormInterface
    {
        return $this->createBulkForm(
            $this->getBulkPublishRouteName(),
            'bulk-publish',
            $get,
            $data
        );
    }

    protected function createUnpublishBulkForm(bool $get = false, ?array $data = null): FormInterface
    {
        return $this->createBulkForm(
            $this->getBulkUnpublishRouteName(),
            'bulk-unpublish',
            $get,
            $data
        );
    }
}
