<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Importer\GroupsImporter;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class GroupsUtilsController extends RozierApp
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly GroupsImporter $groupsImporter,
    ) {
    }

    /**
     * Export all Group data and roles in a Json file (.json).
     */
    public function exportAllAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_GROUPS');

        $existingGroup = $this->em()
                              ->getRepository(Group::class)
                              ->findAll();

        return new JsonResponse(
            $this->serializer->serialize(
                $existingGroup,
                'json',
                SerializationContext::create()->setGroups(['group'])
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf('attachment; filename="%s"', 'group-all-'.date('YmdHis').'.json'),
            ],
            true
        );
    }

    /**
     * Export a Group in a Json file (.json).
     */
    public function exportAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_GROUPS');

        $existingGroup = $this->em()->find(Group::class, $id);

        if (null === $existingGroup) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse(
            $this->serializer->serialize(
                [$existingGroup], // need to wrap in array
                'json',
                SerializationContext::create()->setGroups(['group'])
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf('attachment; filename="%s"', 'group-'.$existingGroup->getName().'-'.date('YmdHis').'.json'),
            ],
            true
        );
    }

    /**
     * Import a Json file (.rzt) containing Group datas and roles.
     *
     * @throws RuntimeError
     */
    public function importJsonFileAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_GROUPS');

        $form = $this->buildImportJsonFileForm();

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && !empty($form['group_file'])
        ) {
            /** @var UploadedFile $file */
            $file = $form['group_file']->getData();

            if ($file->isValid()) {
                $serializedData = file_get_contents($file->getPathname());
                if (false === $serializedData) {
                    throw new RuntimeError('Cannot read uploaded file.');
                }

                if (null !== \json_decode($serializedData)) {
                    $this->groupsImporter->import($serializedData);
                    $this->em()->flush();

                    $msg = $this->getTranslator()->trans('group.imported.updated');
                    $this->publishConfirmMessage($request, $msg);

                    // redirect even if its null
                    return $this->redirectToRoute(
                        'groupsHomePage'
                    );
                }
                $form->addError(new FormError($this->getTranslator()->trans('file.format.not_valid')));
            } else {
                $form->addError(new FormError($this->getTranslator()->trans('file.not_uploaded')));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/groups/import.html.twig', $this->assignation);
    }

    private function buildImportJsonFileForm(): FormInterface
    {
        $builder = $this->createFormBuilder()
                        ->add('group_file', FileType::class, [
                            'label' => 'group.file',
                        ]);

        return $builder->getForm();
    }
}
