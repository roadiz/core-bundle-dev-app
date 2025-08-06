<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Attribute;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Form\AttributeImportType;
use RZ\Roadiz\CoreBundle\Form\AttributeType;
use RZ\Roadiz\CoreBundle\Importer\AttributeImporter;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Controller\AbstractAdminWithBulkController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AttributeController extends AbstractAdminWithBulkController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly AttributeImporter $attributeImporter,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        EntityListManagerFactoryInterface $entityListManagerFactory,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        LogTrail $logTrail,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($formFactory, $urlGenerator, $entityListManagerFactory, $managerRegistry, $translator, $logTrail, $eventDispatcher);
    }

    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Attribute;
    }

    #[\Override]
    protected function getBulkDeleteRouteName(): ?string
    {
        return 'attributesBulkDeletePage';
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'attribute';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        $item = new Attribute();
        $item->setCode('new_attribute');

        return $item;
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/attributes';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES';
    }

    #[\Override]
    protected function getRequiredDeletionRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES_DELETE';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return Attribute::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return AttributeType::class;
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return [
            'weight' => 'DESC',
            'code' => 'ASC',
        ];
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'attributesHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'attributesEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Attribute) {
            return $item->getCode();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    public function importAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_ATTRIBUTES');

        $form = $this->createForm(AttributeImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $filesystem = new Filesystem();

            if ($file->isValid()) {
                $serializedData = $filesystem->readFile($file->getPathname());

                $this->attributeImporter->import($serializedData);
                $this->em()->flush();

                $msg = $this->translator->trans(
                    '%namespace%.imported',
                    [
                        '%namespace%' => $this->translator->trans($this->getNamespace()),
                    ]
                );
                $this->logTrail->publishConfirmMessage($request, $msg);

                return $this->redirectToRoute('attributesHomePage');
            }
            $form->addError(new FormError($this->translator->trans('file.not_uploaded')));
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/attributes/import.html.twig', $this->assignation);
    }

    public function exportAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted($this->getRequiredListingRole());
        $this->additionalAssignation($request);

        $items = $this->getRepository()->findAll();

        return new JsonResponse(
            $this->serializer->serialize(
                $items,
                'json',
                ['groups' => [$this->getNamespace().':export']]
            ),
            Response::HTTP_OK,
            [
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s_%s.json"',
                    $this->getNamespace(),
                    (new \DateTime())->format('YmdHi')
                ),
            ],
            true
        );
    }
}
