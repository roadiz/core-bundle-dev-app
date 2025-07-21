<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Attributes;

use JMS\Serializer\SerializerInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Attribute;
use RZ\Roadiz\CoreBundle\Form\AttributeImportType;
use RZ\Roadiz\CoreBundle\Form\AttributeType;
use RZ\Roadiz\CoreBundle\Importer\AttributeImporter;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Themes\Rozier\Controllers\AbstractAdminWithBulkController;
use Twig\Error\RuntimeError;

final class AttributeController extends AbstractAdminWithBulkController
{
    public function __construct(
        private readonly SymfonySerializerInterface $symfonySerializer,
        private readonly AttributeImporter $attributeImporter,
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($formFactory, $serializer, $urlGenerator);
    }

    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Attribute;
    }

    protected function getBulkDeleteRouteName(): ?string
    {
        return 'attributesBulkDeletePage';
    }

    protected function getNamespace(): string
    {
        return 'attribute';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        $item = new Attribute();
        $item->setCode('new_attribute');

        return $item;
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/attributes';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES';
    }

    protected function getRequiredDeletionRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES_DELETE';
    }

    protected function getEntityClass(): string
    {
        return Attribute::class;
    }

    protected function getFormType(): string
    {
        return AttributeType::class;
    }

    protected function getDefaultOrder(Request $request): array
    {
        return [
            'weight' => 'DESC',
            'code' => 'ASC',
        ];
    }

    protected function getDefaultRouteName(): string
    {
        return 'attributesHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'attributesEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Attribute) {
            return $item->getCode();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    /**
     * @throws RuntimeError
     */
    public function importAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_ATTRIBUTES');

        $form = $this->createForm(AttributeImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            if ($file->isValid()) {
                $serializedData = \file_get_contents($file->getPathname());
                if (false === $serializedData) {
                    throw new \RuntimeException('Cannot read uploaded file.');
                }

                $this->attributeImporter->import($serializedData);
                $this->em()->flush();

                $msg = $this->getTranslator()->trans(
                    '%namespace%.imported',
                    [
                        '%namespace%' => $this->getTranslator()->trans($this->getNamespace()),
                    ]
                );
                $this->publishConfirmMessage($request, $msg);

                return $this->redirectToRoute('attributesHomePage');
            }
            $form->addError(new FormError($this->getTranslator()->trans('file.not_uploaded')));
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/attributes/import.html.twig', $this->assignation);
    }

    public function exportAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted($this->getRequiredExportRole());
        $this->additionalAssignation($request);

        $items = $this->getRepository()->findAll();

        return new JsonResponse(
            $this->symfonySerializer->serialize(
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
