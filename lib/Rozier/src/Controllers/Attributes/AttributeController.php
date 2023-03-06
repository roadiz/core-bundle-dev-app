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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Themes\Rozier\Controllers\AbstractAdminController;

class AttributeController extends AbstractAdminController
{
    private AttributeImporter $attributeImporter;

    public function __construct(
        AttributeImporter $attributeImporter,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($serializer, $urlGenerator);
        $this->attributeImporter = $attributeImporter;
    }


    /**
     * @inheritDoc
     */
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof Attribute;
    }

    /**
     * @inheritDoc
     */
    protected function getNamespace(): string
    {
        return 'attribute';
    }

    /**
     * @inheritDoc
     */
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        $item = new Attribute();
        $item->setCode('new_attribute');
        return $item;
    }

    /**
     * @inheritDoc
     */
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/attributes';
    }

    /**
     * @inheritDoc
     */
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES';
    }

    /**
     * @inheritDoc
     */
    protected function getRequiredDeletionRole(): string
    {
        return 'ROLE_ACCESS_ATTRIBUTES_DELETE';
    }


    /**
     * @inheritDoc
     */
    protected function getEntityClass(): string
    {
        return Attribute::class;
    }

    /**
     * @inheritDoc
     */
    protected function getFormType(): string
    {
        return AttributeType::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultOrder(Request $request): array
    {
        return ['code' => 'ASC'];
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultRouteName(): string
    {
        return 'attributesHomePage';
    }

    /**
     * @inheritDoc
     */
    protected function getEditRouteName(): string
    {
        return 'attributesEditPage';
    }

    /**
     * @inheritDoc
     */
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof Attribute) {
            return $item->getCode();
        }
        throw new \InvalidArgumentException('Item should be instance of ' . $this->getEntityClass());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function importAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_ATTRIBUTES');

        $form = $this->createForm(AttributeImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            if ($file->isValid()) {
                $serializedData = file_get_contents($file->getPathname());

                $this->attributeImporter->import($serializedData);
                $this->em()->flush();
                return $this->redirectToRoute('attributesHomePage');
            }
            $form->addError(new FormError($this->getTranslator()->trans('file.not_uploaded')));
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('@RoadizRozier/attributes/import.html.twig', $this->assignation);
    }
}
