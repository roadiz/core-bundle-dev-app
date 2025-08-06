<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\Setting\SettingCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Setting\SettingDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Setting\SettingUpdatedEvent;
use RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException;
use RZ\Roadiz\CoreBundle\Form\Error\FormErrorSerializer;
use RZ\Roadiz\CoreBundle\Form\SettingType;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\ListManager\SessionListFilters;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class SettingController extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly FormErrorSerializer $formErrorSerializer,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly EntityListManagerFactoryInterface $entityListManagerFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
        private readonly Settings $settingsBag,
    ) {
    }

    /**
     * List every setting.
     */
    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');
        $assignation = [];

        if (null !== $response = $this->commonSettingList($request, null, $assignation)) {
            return $response->send();
        }

        return $this->render('@RoadizRozier/settings/list.html.twig', $assignation);
    }

    public function byGroupAction(Request $request, int $settingGroupId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        /** @var SettingGroup|null $settingGroup */
        $settingGroup = $this->managerRegistry
            ->getRepository(SettingGroup::class)
            ->find($settingGroupId);

        if (null === $settingGroup) {
            throw new ResourceNotFoundException();
        }

        $assignation['settingGroup'] = $settingGroup;

        if (null !== $response = $this->commonSettingList($request, $settingGroup, $assignation)) {
            return $response->send();
        }

        return $this->render('@RoadizRozier/settings/list.html.twig', $assignation);
    }

    protected function commonSettingList(Request $request, ?SettingGroup $settingGroup, array &$assignation): ?Response
    {
        $criteria = [];
        if (null !== $settingGroup) {
            $criteria = ['settingGroup' => $settingGroup];
        }
        /*
         * Manage get request to filter list
         */
        $listManager = $this->entityListManagerFactory->createAdminEntityListManager(
            Setting::class,
            $criteria,
            ['name' => 'ASC']
        );
        $sessionListFilter = new SessionListFilters('settings_item_per_page');
        $sessionListFilter->handleItemPerPage($request, $listManager);

        $listManager->handle();

        $assignation['filters'] = $listManager->getAssignation();
        $settings = $listManager->getEntities();
        $assignation['settings'] = [];
        $isJson =
            $request->isXmlHttpRequest()
            || 'json' === $request->getRequestFormat('html')
            || \in_array(
                'application/json',
                $request->getAcceptableContentTypes()
            );

        /** @var Setting $setting */
        foreach ($settings as $setting) {
            $return = $this->handleSingleSettingForm($request, $setting, $settingGroup, $isJson);
            if ($return instanceof Response) {
                return $return;
            }
            $assignation['settings'][] = $return;
        }

        return null;
    }

    private function handleSingleSettingForm(
        Request $request,
        Setting $setting,
        ?SettingGroup $settingGroup,
        bool $isJson,
    ): array|Response {
        $form = $this->formFactory->createNamed($setting->getName(), SettingType::class, $setting, [
            'shortEdit' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $this->resetSettingsCache();
                    $this->eventDispatcher->dispatch(new SettingUpdatedEvent($setting));
                    $this->managerRegistry->getManagerForClass(Setting::class)->flush();
                    $msg = $this->translator->trans(
                        'setting.%name%.updated',
                        ['%name%' => $setting->getName()]
                    );
                    $this->logTrail->publishConfirmMessage($request, $msg, $setting);

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
            // Form can be invalidated during persistence process
            if (!$form->isValid()) {
                $errors = $this->formErrorSerializer->getErrorsAsArray($form);
                /*
                 * Do not publish any message, it may lead to flushing invalid form
                 */
                if ($isJson) {
                    return new JsonResponse([
                        'status' => 'failed',
                        'errors' => $errors,
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }

        $document = null;
        if ($setting->isDocuments()) {
            $document = $this->settingsBag->getDocument($setting->getName());
        }

        return [
            'setting' => $setting,
            'form' => $form->createView(),
            'document' => $document,
        ];
    }

    /**
     * Return an edition form for requested setting.
     */
    public function editAction(Request $request, int $settingId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');
        /** @var Setting|null $setting */
        $setting = $this->managerRegistry
            ->getRepository(Setting::class)
            ->find($settingId);

        if (null === $setting) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(SettingType::class, $setting, [
            'shortEdit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->resetSettingsCache();
                $this->eventDispatcher->dispatch(new SettingUpdatedEvent($setting));
                $this->managerRegistry->getManagerForClass(Setting::class)->flush();
                $msg = $this->translator->trans('setting.%name%.updated', ['%name%' => $setting->getName()]);
                $this->logTrail->publishConfirmMessage($request, $msg, $setting);

                return $this->redirectToRoute(
                    'settingsEditPage',
                    ['settingId' => $setting->getId()]
                );
            } catch (\RuntimeException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('@RoadizRozier/settings/edit.html.twig', [
            'form' => $form->createView(),
            'setting' => $setting,
        ]);
    }

    protected function resetSettingsCache(): void
    {
        $this->settingsBag->reset();
        /** @var CacheProvider|null $cacheDriver */
        $cacheDriver = $this->managerRegistry
            ->getManagerForClass(Setting::class)
            ->getConfiguration()
            ->getResultCacheImpl();
        $cacheDriver?->deleteAll();
        $this->eventDispatcher->dispatch(new CachePurgeRequestEvent());
    }

    /**
     * Return a creation form for requested setting.
     */
    public function addAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        $setting = new Setting();
        $setting->setSettingGroup(null);

        $form = $this->createForm(SettingType::class, $setting, [
            'shortEdit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->eventDispatcher->dispatch(new SettingCreatedEvent($setting));
                $this->resetSettingsCache();
                $this->managerRegistry->getManagerForClass(Setting::class)->persist($setting);
                $this->managerRegistry->getManagerForClass(Setting::class)->flush();
                $msg = $this->translator->trans('setting.%name%.created', ['%name%' => $setting->getName()]);
                $this->logTrail->publishConfirmMessage($request, $msg, $setting);

                return $this->redirectToRoute('settingsHomePage');
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('@RoadizRozier/settings/add.html.twig', [
            'form' => $form->createView(),
            'setting' => $setting,
        ]);
    }

    /**
     * Return a deletion form for requested setting.
     */
    public function deleteAction(Request $request, int $settingId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        /** @var Setting|null $setting */
        $setting = $this->managerRegistry
            ->getRepository(Setting::class)
            ->find($settingId);

        if (null === $setting) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(FormType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->eventDispatcher->dispatch(new SettingDeletedEvent($setting));
                $this->resetSettingsCache();
                $this->managerRegistry->getManagerForClass(Setting::class)->remove($setting);
                $this->managerRegistry->getManagerForClass(Setting::class)->flush();

                $msg = $this->translator->trans('setting.%name%.deleted', ['%name%' => $setting->getName()]);
                $this->logTrail->publishConfirmMessage($request, $msg, $setting);

                return $this->redirectToRoute('settingsHomePage');
            } catch (\RuntimeException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('@RoadizRozier/settings/delete.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),
        ]);
    }
}
