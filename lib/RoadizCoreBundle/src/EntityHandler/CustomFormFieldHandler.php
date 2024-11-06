<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\CustomFormField;

/**
 * Handle operations with customForms fields entities.
 */
final class CustomFormFieldHandler extends AbstractHandler
{
    private ?CustomFormField $customFormField = null;

    public function __construct(
        ObjectManager $objectManager,
        private readonly CustomFormHandler $customFormHandler,
    ) {
        parent::__construct($objectManager);
    }

    /**
     * @return $this
     */
    public function setCustomFormField(CustomFormField $customFormField): self
    {
        $this->customFormField = $customFormField;

        return $this;
    }

    /**
     * Clean position for current customForm siblings.
     *
     * @return float Return the next position after the **last** customFormField
     */
    public function cleanPositions(bool $setPositions = true): float
    {
        if (null === $this->customFormField) {
            throw new \BadMethodCallException('CustomForm is null');
        }

        $this->customFormHandler->setCustomForm($this->customFormField->getCustomForm());

        return $this->customFormHandler->cleanFieldsPositions($setPositions);
    }
}
