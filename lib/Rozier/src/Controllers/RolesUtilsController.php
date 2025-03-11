<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Importer\RolesImporter;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\RuntimeError;

#[AsController]
final class RolesUtilsController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly SerializerInterface $serializer,
        private readonly LogTrail $logTrail,
        private readonly RolesImporter $rolesImporter,
    ) {
    }

    public function exportAllAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_ROLES');

        $items = $this->managerRegistry->getRepository(Role::class)->findAll();

        return new JsonResponse(
            $this->serializer->serialize(
                $items,
                'json',
                ['groups' => ['role:export']]
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf(
                    'attachment; filename="role_%s.json"',
                    (new \DateTime())->format('YmdHi')
                ),
            ],
            true
        );
    }

    /**
     * Export a Role in a Json file.
     */
    public function exportAction(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_ROLES');

        /** @var Role|null $existingRole */
        $existingRole = $this->managerRegistry->getRepository(Role::class)->find($id);

        if (null === $existingRole) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse(
            $this->serializer->serialize(
                [$existingRole],
                'json',
                ['groups' => ['role:export']]
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf('attachment; filename="%s"', 'role-'.$existingRole->getRole().'-'.date('YmdHis').'.json'),
            ],
            true
        );
    }

    /**
     * Import a Json file containing Roles.
     *
     * @throws RuntimeError
     */
    public function importJsonFileAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_ROLES');

        $form = $this->buildImportJsonFileForm();
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
            && !empty($form['role_file'])
        ) {
            $file = $form['role_file']->getData();

            if ($file->isValid()) {
                $serializedData = file_get_contents($file->getPathname());
                if (false === $serializedData) {
                    throw new RuntimeError('Cannot read uploaded file.');
                }

                if (null !== \json_decode($serializedData)) {
                    if ($this->rolesImporter->import($serializedData)) {
                        $msg = $this->translator->trans('role.imported');
                        $this->logTrail->publishConfirmMessage($request, $msg);

                        $manager = $this->managerRegistry->getManagerForClass(Role::class);
                        $manager->flush();

                        // Clear result cache
                        $cacheDriver = $manager->getConfiguration()->getResultCacheImpl();
                        if ($cacheDriver instanceof CacheProvider) {
                            $cacheDriver->deleteAll();
                        }

                        // redirect even if its null
                        return $this->redirectToRoute(
                            'rolesHomePage'
                        );
                    }
                }
                $form->addError(new FormError($this->translator->trans('file.format.not_valid')));
            } else {
                $form->addError(new FormError($this->translator->trans('file.not_uploaded')));
            }
        }

        return $this->render('@RoadizRozier/roles/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function buildImportJsonFileForm(): FormInterface
    {
        $builder = $this->createFormBuilder()
                        ->add('role_file', FileType::class, [
                            'label' => 'role.file',
                        ]);

        return $builder->getForm();
    }
}
