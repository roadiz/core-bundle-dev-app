<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Importer\SettingsImporter;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\SettingGroup;
use RZ\Roadiz\Utils\StringHandler;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class SettingsUtilsController extends RozierApp
{
    private SerializerInterface $serializer;
    private SettingsImporter $settingsImporter;

    /**
     * @param SerializerInterface $serializer
     * @param SettingsImporter $settingsImporter
     */
    public function __construct(SerializerInterface $serializer, SettingsImporter $settingsImporter)
    {
        $this->serializer = $serializer;
        $this->settingsImporter = $settingsImporter;
    }

    /**
     * Export all settings in a Json file.
     *
     * @param Request $request
     * @param int|null $settingGroupId
     *
     * @return Response
     */
    public function exportAllAction(Request $request, ?int $settingGroupId = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        if (null !== $settingGroupId) {
            /** @var SettingGroup|null $group */
            $group = $this->em()->find(SettingGroup::class, $settingGroupId);
            if (null === $group) {
                throw $this->createNotFoundException();
            }
            $fileName = 'settings-' . \mb_strtolower(StringHandler::cleanForFilename($group->getName())) . '-' . date("YmdHis") . '.json';
            $settings = $this->em()
                ->getRepository(Setting::class)
                ->findBySettingGroup($group);
        } else {
            $fileName = 'settings-' . date("YmdHis") . '.json';
            $settings = $this->em()
                ->getRepository(Setting::class)
                ->findAll();
        }

        return new JsonResponse(
            $this->serializer->serialize(
                $settings,
                'json',
                SerializationContext::create()->setGroups(['setting'])
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
            ],
            true
        );
    }

    /**
     * Import a Json file (.rzt) containing setting and setting group.
     *
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeError
     */
    public function importJsonFileAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_SETTINGS');

        $form = $this->buildImportJsonFileForm();

        $form->handleRequest($request);

        if (
            $form->isSubmitted() &&
            $form->isValid() &&
            !empty($form['setting_file'])
        ) {
            $file = $form['setting_file']->getData();

            if ($file->isValid()) {
                $serializedData = file_get_contents($file->getPathname());

                if (!\is_string($serializedData)) {
                    throw new RuntimeError('Imported file is not a string.');
                }

                if (null !== \json_decode($serializedData)) {
                    if ($this->settingsImporter->import($serializedData)) {
                        $msg = $this->getTranslator()->trans('setting.imported');
                        $this->publishConfirmMessage($request, $msg);
                        $this->em()->flush();

                        // redirect even if its null
                        return $this->redirectToRoute(
                            'settingsHomePage'
                        );
                    }
                }
                $form->addError(new FormError($this->getTranslator()->trans('file.format.not_valid')));
            } else {
                $form->addError(new FormError($this->getTranslator()->trans('file.not_uploaded')));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/settings/import.html.twig', $this->assignation);
    }

    /**
     * @return FormInterface
     */
    private function buildImportJsonFileForm(): FormInterface
    {
        $builder = $this->createFormBuilder()
                        ->add('setting_file', FileType::class, [
                            'label' => 'settingFile',
                        ]);

        return $builder->getForm();
    }
}
