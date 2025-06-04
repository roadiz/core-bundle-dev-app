<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\NodesSources;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\EventDispatcher\Event;

final class NodesSourcesPathGeneratingEvent extends Event
{
    private ?string $path = null;
    /**
     * @var bool tells Node Router to prepend request context information to path or not
     */
    private bool $isComplete = false;
    protected bool $containsScheme = false;

    public function __construct(
        private ?NodesSources $nodeSource,
        private readonly ?RequestContext $requestContext,
        private array $parameters = [],
        private readonly bool $forceLocale = false,
        private bool $forceLocaleWithUrlAlias = false,
    ) {
    }

    public function getNodeSource(): ?NodesSources
    {
        return $this->nodeSource;
    }

    public function setNodeSource(?NodesSources $nodeSource): NodesSourcesPathGeneratingEvent
    {
        $this->nodeSource = $nodeSource;

        return $this;
    }

    public function getRequestContext(): ?RequestContext
    {
        return $this->requestContext;
    }

    public function isForceLocale(): bool
    {
        return $this->forceLocale;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): NodesSourcesPathGeneratingEvent
    {
        $this->path = $path;

        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): NodesSourcesPathGeneratingEvent
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function isComplete(): bool
    {
        return $this->isComplete;
    }

    public function setComplete(bool $isComplete): NodesSourcesPathGeneratingEvent
    {
        $this->isComplete = $isComplete;

        return $this;
    }

    public function containsScheme(): bool
    {
        return $this->containsScheme;
    }

    public function setContainsScheme(bool $containsScheme): NodesSourcesPathGeneratingEvent
    {
        $this->containsScheme = $containsScheme;

        return $this;
    }

    public function isForceLocaleWithUrlAlias(): bool
    {
        return $this->forceLocaleWithUrlAlias;
    }

    public function setForceLocaleWithUrlAlias(bool $forceLocaleWithUrlAlias): NodesSourcesPathGeneratingEvent
    {
        $this->forceLocaleWithUrlAlias = $forceLocaleWithUrlAlias;

        return $this;
    }
}
