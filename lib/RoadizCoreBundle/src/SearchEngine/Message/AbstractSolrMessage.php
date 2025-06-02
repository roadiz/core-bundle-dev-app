<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

abstract class AbstractSolrMessage implements AsyncMessage
{
    public function __construct(
        /**
         * Cannot typehint with class-string: breaks Symfony Serializer 5.4.
         */
        protected string $classname,
        protected mixed $identifier,
    ) {
    }

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function getIdentifier(): mixed
    {
        return $this->identifier;
    }
}
