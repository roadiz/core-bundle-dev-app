<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

interface DateTimedInterface
{
    public function getCreatedAt(): ?\DateTime;

    /**
     * @return $this
     */
    public function setCreatedAt(?\DateTime $createdAt): self;

    public function getUpdatedAt(): ?\DateTime;

    /**
     * @return $this
     */
    public function setUpdatedAt(?\DateTime $updatedAt): self;
}
