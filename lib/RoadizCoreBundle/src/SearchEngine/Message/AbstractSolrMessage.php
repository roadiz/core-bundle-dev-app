<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;

abstract class AbstractSolrMessage implements AsyncMessage
{
    /**
     * @var class-string
     */
    protected string $classname;
    /**
     * @var mixed
     */
    protected mixed $identifier;

    /**
     * @param class-string $classname
     * @param mixed $identifier
     */
    public function __construct(string $classname, mixed $identifier)
    {
        $this->classname = $classname;
        $this->identifier = $identifier;
    }

    /**
     * @return class-string
     */
    public function getClassname(): string
    {
        return $this->classname;
    }

    /**
     * @return mixed
     */
    public function getIdentifier(): mixed
    {
        return $this->identifier;
    }
}
