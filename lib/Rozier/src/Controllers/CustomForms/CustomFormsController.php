<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\CustomForms;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\HttpFoundation\Request;
use Themes\Rozier\Controllers\AbstractAdminWithBulkController;
use Themes\Rozier\Forms\CustomFormType;

class CustomFormsController extends AbstractAdminWithBulkController
{
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof CustomForm;
    }

    protected function getNamespace(): string
    {
        return 'custom-form';
    }

    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new CustomForm();
    }

    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/custom-forms';
    }

    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_CUSTOMFORMS';
    }

    protected function getEntityClass(): string
    {
        return CustomForm::class;
    }

    protected function getFormType(): string
    {
        return CustomFormType::class;
    }

    protected function getDefaultOrder(Request $request): array
    {
        return ['createdAt' => 'DESC'];
    }

    protected function getDefaultRouteName(): string
    {
        return 'customFormsHomePage';
    }

    protected function getEditRouteName(): string
    {
        return 'customFormsEditPage';
    }

    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof CustomForm) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of ' . $this->getEntityClass());
    }

    protected function getBulkDeleteRouteName(): ?string
    {
        return 'customFormsBulkDeletePage';
    }
}
