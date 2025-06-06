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
    #[\Override]
    protected function supports(PersistableInterface $item): bool
    {
        return $item instanceof CustomForm;
    }

    #[\Override]
    protected function getNamespace(): string
    {
        return 'custom-form';
    }

    #[\Override]
    protected function createEmptyItem(Request $request): PersistableInterface
    {
        return new CustomForm();
    }

    #[\Override]
    protected function getTemplateFolder(): string
    {
        return '@RoadizRozier/custom-forms';
    }

    #[\Override]
    protected function getRequiredRole(): string
    {
        return 'ROLE_ACCESS_CUSTOMFORMS';
    }

    #[\Override]
    protected function getEntityClass(): string
    {
        return CustomForm::class;
    }

    #[\Override]
    protected function getFormType(): string
    {
        return CustomFormType::class;
    }

    #[\Override]
    protected function getDefaultOrder(Request $request): array
    {
        return ['createdAt' => 'DESC'];
    }

    #[\Override]
    protected function getDefaultRouteName(): string
    {
        return 'customFormsHomePage';
    }

    #[\Override]
    protected function getEditRouteName(): string
    {
        return 'customFormsEditPage';
    }

    #[\Override]
    protected function getEntityName(PersistableInterface $item): string
    {
        if ($item instanceof CustomForm) {
            return $item->getName();
        }
        throw new \InvalidArgumentException('Item should be instance of '.$this->getEntityClass());
    }

    #[\Override]
    protected function getBulkDeleteRouteName(): ?string
    {
        return 'customFormsBulkDeletePage';
    }
}
