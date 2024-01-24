<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;

final class NullLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    public function load(mixed $resource, string $type = null): mixed
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource, string $type = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getResolver(): ?LoaderResolverInterface
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setResolver(LoaderResolverInterface $resolver): self
    {
        return $this;
    }
}
