<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\CustomForms;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\RozierBundle\Form\CustomFormType;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Controllers\AbstractAdminController;

class CustomFormsController extends AbstractAdminController
{
    /**
     * @inheritDoc
     */
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof CustomForm;
    }

    /**
     * @inheritDoc
     */
    protected function getNamespace(): string
    {
        return 'custom-form';
    }

    /**
     * @inheritDoc
     */
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new CustomForm();
    }

    /**
     * @inheritDoc
     */
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/custom-forms';
    }

    /**
     * @inheritDoc
     */
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_CUSTOMFORMS';
    }

    /**
     * @inheritDoc
     */
    protected function getEntityClass(): string
    {
        return CustomForm::class;
    }

    /**
     * @inheritDoc
     */
    protected function getFormType(): string
    {
        return CustomFormType::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultOrder(Request $request): array
    {
        return ['createdAt' => 'DESC'];
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultRouteName(): string
    {
        return 'customFormsHomePage';
    }

    /**
     * @inheritDoc
     */
    protected function getEditRouteName(): string
    {
        return 'customFormsEditPage';
    }

    /**
     * @inheritDoc
     */
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof CustomForm) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of ' . $this->getEntityClass());
    }
}
