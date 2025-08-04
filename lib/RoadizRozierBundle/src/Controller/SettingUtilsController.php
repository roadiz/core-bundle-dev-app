<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\CoreBundle\Importer\SettingsImporter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class SettingUtilsController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly SettingsImporter $settingsImporter,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    /**
     * Export all settings in a Json file.
     */
    public function exportAllAction(Request $request, ?int $settingGroupId = null): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        if (null !== $settingGroupId) {
            /** @var SettingGroup|null $group */
            $group = $this->managerRegistry
                ->getRepository(SettingGroup::class)
                ->find($settingGroupId);
            if (null === $group) {
                throw $this->createNotFoundException();
            }
            $fileName = 'settings-'.\mb_strtolower(StringHandler::cleanForFilename($group->getName())).'-'.date('YmdHis').'.json';
            $settings = $this->managerRegistry
                ->getRepository(Setting::class)
                ->findBySettingGroup($group);
        } else {
            $fileName = 'settings-'.date('YmdHis').'.json';
            $settings = $this->managerRegistry
                ->getRepository(Setting::class)
                ->findAll();
        }

        return new JsonResponse(
            $this->serializer->serialize(
                $settings,
                'json',
                [
                    'groups' => ['setting'],
                ]
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
            ],
            true
        );
    }

    public function importJsonFileAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        $form = $this->buildImportJsonFileForm();
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && !empty($form['setting_file'])
        ) {
            $file = $form['setting_file']->getData();
            $filesystem = new Filesystem();

            if ($file->isValid()) {
                $serializedData = $filesystem->readFile($file->getPathname());

                if (null !== \json_decode($serializedData)) {
                    if ($this->settingsImporter->import($serializedData)) {
                        $msg = $this->translator->trans('setting.imported');
                        $this->logTrail->publishConfirmMessage($request, $msg);
                        $this->managerRegistry->getManagerForClass(Setting::class)->flush();

                        // redirect even if its null
                        return $this->redirectToRoute(
                            'settingsHomePage'
                        );
                    }
                }
                $form->addError(new FormError($this->translator->trans('file.format.not_valid')));
            } else {
                $form->addError(new FormError($this->translator->trans('file.not_uploaded')));
            }
        }

        return $this->render('@RoadizRozier/settings/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function buildImportJsonFileForm(): FormInterface
    {
        $builder = $this->createFormBuilder()
                        ->add('setting_file', FileType::class, [
                            'label' => 'settingFile',
                        ]);

        return $builder->getForm();
    }
}
