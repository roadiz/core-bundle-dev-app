<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use Psr\Container\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractExplorerProvider implements ExplorerProviderInterface
{
    protected array $options;

    /**
     * @deprecated
     */
    protected ContainerInterface $container;

    /**
     * @deprecated
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'page' => 1,
            'search' => null,
            'itemPerPage' => 30,
        ]);
    }
}
