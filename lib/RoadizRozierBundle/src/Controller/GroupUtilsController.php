<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Importer\GroupsImporter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\RuntimeError;

#[AsController]
final class GroupUtilsController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly GroupsImporter $groupsImporter,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    /**
     * Export all Group data and roles in a Json file (.json).
     */
    public function exportAllAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_GROUPS');

        $groups = $this->managerRegistry
                              ->getRepository(Group::class)
                              ->findAll();

        return new JsonResponse(
            $this->serializer->serialize(
                $groups,
                'json',
                ['groups' => ['group:export']]
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

        $existingGroup = $this->managerRegistry->getRepository(Group::class)->find($id);

        if (null === $existingGroup) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse(
            $this->serializer->serialize(
                [$existingGroup], // need to wrap in array
                'json',
                ['groups' => ['group:export']],
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
            $filesystem = new Filesystem();

            if ($file->isValid()) {
                $serializedData = $filesystem->readFile($file->getPathname());

                if (null !== \json_decode($serializedData)) {
                    $this->groupsImporter->import($serializedData);
                    $this->managerRegistry->getManager()->flush();

                    $msg = $this->translator->trans('group.imported.updated');
                    $this->logTrail->publishConfirmMessage($request, $msg);

                    // redirect even if its null
                    return $this->redirectToRoute(
                        'groupsHomePage'
                    );
                }
                $form->addError(new FormError($this->translator->trans('file.format.not_valid')));
            } else {
                $form->addError(new FormError($this->translator->trans('file.not_uploaded')));
            }
        }

        return $this->render('@RoadizRozier/groups/import.html.twig', [
            'form' => $form->createView(),
        ]);
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
