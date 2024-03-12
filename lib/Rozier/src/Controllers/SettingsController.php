<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Doctrine\Common\Cache\CacheProvider;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\Setting\SettingCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Setting\SettingDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Setting\SettingUpdatedEvent;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\Form\SettingType;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class SettingsController extends RozierApp
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly FormErrorSerializer $formErrorSerializer
    ) {
    }

    /**
     * List every setting.
     *
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     */
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        if (null !== $response = $this->commonSettingList($request)) {
            return $response->send();
        }

        return $this->render('@RoadizRozier/settings/list.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param int $settingGroupId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function byGroupAction(Request $request, int $settingGroupId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        /** @var SettingGroup|null $settingGroup */
        $settingGroup = $this->em()->find(SettingGroup::class, $settingGroupId);

        if ($settingGroup === null) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['settingGroup'] = $settingGroup;

        if (null !== $response = $this->commonSettingList($request, $settingGroup)) {
            return $response->send();
        }

        return $this->render('@RoadizRozier/settings/list.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     * @param SettingGroup|null $settingGroup
     *
     * @return Response|null
     */
    protected function commonSettingList(Request $request, SettingGroup $settingGroup = null): ?Response
    {
        $criteria = [];
        if (null !== $settingGroup) {
            $criteria = ['settingGroup' => $settingGroup];
        }
        /*
         * Manage get request to filter list
         */
        $listManager = $this->createEntityListManager(
            Setting::class,
            $criteria,
            ['name' => 'ASC']
        );
        $listManager->setDisplayingNotPublishedNodes(true);

        /*
         * Stored in session
         */
        $sessionListFilter = new SessionListFilters('settings_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);

        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $settings = $listManager->getEntities();
        $this->assignation['settings'] = [];
        $isJson =
            $request->isXmlHttpRequest() ||
            $request->getRequestFormat('html') === 'json' ||
            \in_array(
                'application/json',
                $request->getAcceptableContentTypes()
            );

        /** @var Setting $setting */
        foreach ($settings as $setting) {
            $form = $this->formFactory->createNamed($setting->getName(), SettingType::class, $setting, [
                'shortEdit' => true,
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    try {
                        $this->resetSettingsCache();
                        $this->dispatchEvent(new SettingUpdatedEvent($setting));
                        $this->em()->flush();
                        $msg = $this->getTranslator()->trans(
                            'setting.%name%.updated',
                            ['%name%' => $setting->getName()]
                        );
                        $this->publishConfirmMessage($request, $msg, $setting);

                        if ($isJson) {
                            return new JsonResponse([
                                'status' => 'success',
                                'message' => $msg,
                            ], Response::HTTP_ACCEPTED);
                        }

                        if (null !== $settingGroup) {
                            return $this->redirectToRoute(
                                'settingGroupsSettingsPage',
                                ['settingGroupId' => $settingGroup->getId()]
                            );
                        } else {
                            return $this->redirectToRoute(
                                'settingsHomePage'
                            );
                        }
                    } catch (\RuntimeException $e) {
                        $form->addError(new FormError($e->getMessage()));
                    }
                }
                // Form can be invalidated during persistance process
                if (!$form->isValid()) {
                    $errors = $this->formErrorSerializer->getErrorsAsArray($form);
                    /*
                     * Do not publish any message, it may lead to flushing invalid form
                     */
                    if ($isJson) {
                        return new JsonResponse([
                            'status' => 'failed',
                            'errors' => $errors,
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            }

            $document = null;
            if ($setting->getType() == AbstractField::DOCUMENTS_T) {
                $document = $this->getSettingsBag()->getDocument($setting->getName());
            }

            $this->assignation['settings'][] = [
                'setting' => $setting,
                'form' => $form->createView(),
                'document' => $document,
            ];
        }

        return null;
    }

    /**
     * Return an edition form for requested setting.
     *
     * @param Request $request
     * @param int $settingId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function editAction(Request $request, int $settingId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');
        /** @var Setting|null $setting */
        $setting = $this->em()->find(Setting::class, $settingId);

        if ($setting === null) {
            throw $this->createNotFoundException();
        }

        $this->assignation['setting'] = $setting;

        $form = $this->createForm(SettingType::class, $setting, [
            'shortEdit' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->resetSettingsCache();
                $this->dispatchEvent(new SettingUpdatedEvent($setting));
                $this->em()->flush();
                $msg = $this->getTranslator()->trans('setting.%name%.updated', ['%name%' => $setting->getName()]);
                $this->publishConfirmMessage($request, $msg, $setting);
                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                return $this->redirectToRoute(
                    'settingsEditPage',
                    ['settingId' => $setting->getId()]
                );
            } catch (\RuntimeException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/settings/edit.html.twig', $this->assignation);
    }

    protected function resetSettingsCache(): void
    {
        $this->getSettingsBag()->reset();
        /** @var CacheProvider|null $cacheDriver */
        $cacheDriver = $this->em()->getConfiguration()->getResultCacheImpl();
        $cacheDriver?->deleteAll();
        $this->dispatchEvent(new CachePurgeRequestEvent());
    }

    /**
     * Return a creation form for requested setting.
     *
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     */
    public function addAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        $setting = new Setting();
        $setting->setSettingGroup(null);

        $this->assignation['setting'] = $setting;
        $form = $this->createForm(SettingType::class, $setting, [
            'shortEdit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->dispatchEvent(new SettingCreatedEvent($setting));
                $this->resetSettingsCache();
                $this->em()->persist($setting);
                $this->em()->flush();
                $msg = $this->getTranslator()->trans('setting.%name%.created', ['%name%' => $setting->getName()]);
                $this->publishConfirmMessage($request, $msg, $setting);

                return $this->redirectToRoute('settingsHomePage');
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/settings/add.html.twig', $this->assignation);
    }

    /**
     * Return a deletion form for requested setting.
     *
     * @param Request $request
     * @param int $settingId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function deleteAction(Request $request, int $settingId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        /** @var Setting|null $setting */
        $setting = $this->em()->find(Setting::class, $settingId);

        if (null === $setting) {
            throw new ResourceNotFoundException();
        }

        $this->assignation['setting'] = $setting;

        $form = $this->createForm(FormType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->dispatchEvent(new SettingDeletedEvent($setting));
                $this->resetSettingsCache();
                $this->em()->remove($setting);
                $this->em()->flush();

                $msg = $this->getTranslator()->trans('setting.%name%.deleted', ['%name%' => $setting->getName()]);
                $this->publishConfirmMessage($request, $msg, $setting);

                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                return $this->redirectToRoute('settingsHomePage');
            } catch (\RuntimeException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/settings/delete.html.twig', $this->assignation);
    }
}
