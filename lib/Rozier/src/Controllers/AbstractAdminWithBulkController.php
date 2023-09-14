<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractAdminWithBulkController extends AbstractAdminController
{
    protected FormFactoryInterface $formFactory;

    public function __construct(
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($serializer, $urlGenerator);
        $this->formFactory = $formFactory;
    }

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

    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredDeletionRole());

        $bulkDeleteForm = $this->createDeleteBulkForm(true);
        $deleteForm = $this->createDeleteBulkForm();
        $bulkDeleteForm->handleRequest($request);
        $deleteForm->handleRequest($request);

        if ($bulkDeleteForm->isSubmitted() && $bulkDeleteForm->isValid()) {
            $ids = json_decode($bulkDeleteForm->get('id')->getData() ?? '[]');
            $ids = array_filter($ids, function ($id) {
                // Allow int or UUID identifiers
                return is_numeric($id) || is_string($id);
            });
            if (count($ids) < 1) {
                $bulkDeleteForm->addError(new FormError('No item selected.'));
            } else {
                $events = $this->em()->getRepository($this->getEntityClass())->findBy([
                    'id' => $ids,
                ]);
                $deleteForm = $this->createDeleteBulkForm(false, [
                    'id' => json_encode($ids),
                ]);

                $this->assignation['items'] = $events;
                $this->assignation['filters'] = ['itemCount' => count($events)];
                $this->assignation['form'] = $deleteForm->createView();
            }
        }
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $ids = json_decode($deleteForm->get('id')->getData());
            $ids = array_filter($ids, function ($id) {
                // Allow int or UUID identifiers
                return is_numeric($id) || is_string($id);
            });
            if (count($ids) < 1) {
                $deleteForm->addError(new FormError('No item selected.'));
            } else {
                /** @var PersistableInterface[] $items */
                $items = $this->em()->getRepository($this->getEntityClass())->findBy([
                    'id' => $ids,
                ]);
                foreach ($items as $item) {
                    $msg = $this->getTranslator()->trans(
                        '%namespace%.%item%.was_deleted',
                        [
                            '%item%' => $this->getEntityName($item),
                            '%namespace%' => $this->getTranslator()->trans($this->getNamespace())
                        ]
                    );
                    $this->publishConfirmMessage($request, $msg);
                    $this->em()->remove($item);
                }
                $this->em()->flush();
                return $this->redirect($this->urlGenerator->generate($this->getDefaultRouteName()));
            }
        }

        $this->assignation['bulkDeleteForm'] = $bulkDeleteForm->createView();

        return $this->render(
            $this->getTemplateFolder() . '/bulk_delete.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    public function bulkPublishAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        $bulkPublishForm = $this->createPublishBulkForm(true);
        $publishForm = $this->createPublishBulkForm();
        $bulkPublishForm->handleRequest($request);
        $publishForm->handleRequest($request);

        if ($bulkPublishForm->isSubmitted() && $bulkPublishForm->isValid()) {
            $ids = json_decode($bulkPublishForm->get('id')->getData() ?? '[]');
            $ids = array_filter($ids, function ($id) {
                return is_numeric($id);
            });
            if (count($ids) < 1) {
                $bulkPublishForm->addError(new FormError('No item selected.'));
            } else {
                $events = $this->em()->getRepository($this->getEntityClass())->findBy([
                    'id' => $ids,
                ]);
                $publishForm = $this->createPublishBulkForm(false, [
                    'id' => json_encode($ids),
                ]);

                $this->assignation['items'] = $events;
                $this->assignation['filters'] = ['itemCount' => count($events)];
                $this->assignation['form'] = $publishForm->createView();
            }
        }
        if ($publishForm->isSubmitted() && $publishForm->isValid()) {
            $ids = json_decode($publishForm->get('id')->getData());
            $ids = array_filter($ids, function ($id) {
                return is_numeric($id);
            });
            if (count($ids) < 1) {
                $publishForm->addError(new FormError('No item selected.'));
            } else {
                /** @var PersistableInterface[] $items */
                $items = $this->em()->getRepository($this->getEntityClass())->findBy([
                    'id' => $ids,
                ]);
                foreach ($items as $item) {
                    if ($this->supports($item)) {
                        $this->setPublishedAt($item, new \DateTime('now'));
                        $msg = $this->getTranslator()->trans(
                            '%namespace%.%item%.was_published',
                            [
                                '%item%' => $this->getEntityName($item),
                                '%namespace%' => $this->getTranslator()->trans($this->getNamespace())
                            ]
                        );
                        $this->publishConfirmMessage($request, $msg);
                    }
                }
                $this->em()->flush();
                return $this->redirect($this->urlGenerator->generate($this->getDefaultRouteName()));
            }
        }

        $this->assignation['bulkPublishForm'] = $bulkPublishForm->createView();

        return $this->render(
            $this->getTemplateFolder() . '/bulk_publish.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    public function bulkUnpublishAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->getRequiredRole());

        $bulkUnpublishForm = $this->createUnpublishBulkForm(true);
        $unpublishForm = $this->createUnpublishBulkForm();
        $bulkUnpublishForm->handleRequest($request);
        $unpublishForm->handleRequest($request);

        if ($bulkUnpublishForm->isSubmitted() && $bulkUnpublishForm->isValid()) {
            $ids = json_decode($bulkUnpublishForm->get('id')->getData() ?? '[]');
            $ids = array_filter($ids, function ($id) {
                return is_numeric($id);
            });
            if (count($ids) < 1) {
                $bulkUnpublishForm->addError(new FormError('No item selected.'));
            } else {
                $events = $this->em()->getRepository($this->getEntityClass())->findBy([
                    'id' => $ids,
                ]);
                $unpublishForm = $this->createUnpublishBulkForm(false, [
                    'id' => json_encode($ids),
                ]);

                $this->assignation['items'] = $events;
                $this->assignation['filters'] = ['itemCount' => count($events)];
                $this->assignation['form'] = $unpublishForm->createView();
            }
        }
        if ($unpublishForm->isSubmitted() && $unpublishForm->isValid()) {
            $ids = json_decode($unpublishForm->get('id')->getData());
            $ids = array_filter($ids, function ($id) {
                return is_numeric($id);
            });
            if (count($ids) < 1) {
                $unpublishForm->addError(new FormError('No item selected.'));
            } else {
                /** @var PersistableInterface[] $items */
                $items = $this->em()->getRepository($this->getEntityClass())->findBy([
                    'id' => $ids,
                ]);
                foreach ($items as $item) {
                    if ($this->supports($item)) {
                        $this->setPublishedAt($item, null);
                        $msg = $this->getTranslator()->trans(
                            '%namespace%.%item%.was_unpublished',
                            [
                                '%item%' => $this->getEntityName($item),
                                '%namespace%' => $this->getTranslator()->trans($this->getNamespace())
                            ]
                        );
                        $this->publishConfirmMessage($request, $msg);
                    }
                }
                $this->em()->flush();
                return $this->redirect($this->urlGenerator->generate($this->getDefaultRouteName()));
            }
        }

        $this->assignation['bulkUnpublishForm'] = $bulkUnpublishForm->createView();

        return $this->render(
            $this->getTemplateFolder() . '/bulk_unpublish.html.twig',
            $this->assignation,
            null,
            $this->getTemplateNamespace()
        );
    }

    protected function createDeleteBulkForm(bool $get = false, ?array $data = null): FormInterface
    {
        $routeName = $this->getBulkDeleteRouteName();
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
        return $this->formFactory->createNamedBuilder('bulk-delete', FormType::class, $data, $options)
            ->add('id', HiddenType::class, [
                'attr' => [
                    'class' => 'bulk-form-value'
                ]
            ])->getForm();
    }

    protected function createPublishBulkForm(bool $get = false, ?array $data = null): FormInterface
    {
        $routeName = $this->getBulkPublishRouteName();
        if (null === $routeName) {
            throw new \RuntimeException('Bulk publish route name is not defined.');
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
        return $this->formFactory->createNamedBuilder('bulk-publish', FormType::class, $data, $options)
            ->add('id', HiddenType::class, [
                'attr' => [
                    'class' => 'bulk-form-value'
                ]
            ])->getForm();
    }

    protected function createUnpublishBulkForm(bool $get = false, ?array $data = null): FormInterface
    {
        $routeName = $this->getBulkUnpublishRouteName();
        if (null === $routeName) {
            throw new \RuntimeException('Bulk unpublish route name is not defined.');
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
        return $this->formFactory->createNamedBuilder('bulk-unpublish', FormType::class, $data, $options)
            ->add('id', HiddenType::class, [
                'attr' => [
                    'class' => 'bulk-form-value'
                ]
            ])->getForm();
    }
}
