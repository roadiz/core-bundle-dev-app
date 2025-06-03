<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

abstract class AbstractSolrMessage implements AsyncMessage
{
    /**
     * @param class-string $classname
     */
    public function __construct(
        protected string $classname,
        protected mixed $identifier,
    ) {
    }

    /**
     * @return class-string
     */
    public function getClassname(): string
    {
        return $this->classname;
    }

    public function getIdentifier(): mixed
    {
        return $this->identifier;
    }
}
