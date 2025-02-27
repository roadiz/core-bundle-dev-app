<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Doctrine\Common\Cache\CacheProvider;
use RZ\Roadiz\CoreBundle\Entity\Role;
use RZ\Roadiz\CoreBundle\Importer\RolesImporter;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class RolesUtilsController extends RozierApp
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly RolesImporter $rolesImporter,
    ) {
    }

    public function exportAllAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_ROLES');

        $items = $this->em()->getRepository(Role::class)->findAll();

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
        $existingRole = $this->em()->find(Role::class, $id);

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
                'Content-Disposition' => sprintf('attachment; filename="%s"', 'role-'.$existingRole->getName().'-'.date('YmdHis').'.json'),
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
                        $msg = $this->getTranslator()->trans('role.imported');
                        $this->publishConfirmMessage($request, $msg);

                        $this->em()->flush();

                        // Clear result cache
                        $cacheDriver = $this->em()->getConfiguration()->getResultCacheImpl();
                        if ($cacheDriver instanceof CacheProvider) {
                            $cacheDriver->deleteAll();
                        }

                        // redirect even if its null
                        return $this->redirectToRoute(
                            'rolesHomePage'
                        );
                    }
                }
                $form->addError(new FormError($this->getTranslator()->trans('file.format.not_valid')));
            } else {
                $form->addError(new FormError($this->getTranslator()->trans('file.not_uploaded')));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/roles/import.html.twig', $this->assignation);
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
