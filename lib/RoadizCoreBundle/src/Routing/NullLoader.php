<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;

final class NullLoader implements LoaderInterface
{
    #[\Override]
    public function load(mixed $resource, ?string $type = null): mixed
    {
        return null;
    }

    #[\Override]
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return true;
    }

    #[\Override]
    public function getResolver(): ?LoaderResolverInterface
    {
        return null;
    }

    #[\Override]
    public function setResolver(LoaderResolverInterface $resolver): self
    {
        return $this;
    }
}
