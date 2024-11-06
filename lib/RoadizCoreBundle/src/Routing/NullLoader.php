<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;

final class NullLoader implements LoaderInterface
{
    public function load(mixed $resource, ?string $type = null): mixed
    {
        return null;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return true;
    }

    public function getResolver(): ?LoaderResolverInterface
    {
        return null;
    }

    public function setResolver(LoaderResolverInterface $resolver): self
    {
        return $this;
    }
}
