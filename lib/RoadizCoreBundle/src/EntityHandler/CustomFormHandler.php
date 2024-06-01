<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityHandler;

use Doctrine\Common\Collections\Criteria;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;

/**
 * Handle operations with node-type entities.
 */
final class CustomFormHandler extends AbstractHandler
{
    protected ?CustomForm $customForm = null;

    public function setCustomForm(CustomForm $customForm): self
    {
        $this->customForm = $customForm;
        return $this;
    }

    /**
     * Reset current node-type fields positions.
     *
     * @param bool $setPositions
     * @return float Return the next position after the **last** field
     */
    public function cleanFieldsPositions(bool $setPositions = true): float
    {
        if (null === $this->customForm) {
            throw new \BadMethodCallException('CustomForm is null');
        }

        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        $fields = $this->customForm->getFields()->matching($criteria);
        $i = 1;
        foreach ($fields as $field) {
            if ($setPositions) {
                $field->setPosition($i);
            }
            $i++;
        }

        if ($setPositions) {
            $this->objectManager->flush();
        }

        return $i;
    }
}
