<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\Handlers\AbstractHandler;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactory;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class HandlerExtension extends AbstractExtension
{
    private HandlerFactory $handlerFactory;

    /**
     * @param HandlerFactory $handlerFactory
     */
    public function __construct(HandlerFactory $handlerFactory)
    {
        $this->handlerFactory = $handlerFactory;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('handler', [$this, 'getHandler']),
        ];
    }

    /**
     * @param mixed $mixed
     * @return AbstractHandler|null
     * @throws RuntimeError
     */
    public function getHandler($mixed)
    {
        if (null === $mixed) {
            return null;
        }

        if ($mixed instanceof AbstractEntity) {
            try {
                return $this->handlerFactory->getHandler($mixed);
            } catch (\InvalidArgumentException $exception) {
                throw new RuntimeError($exception->getMessage(), -1, null, $exception);
            }
        }

        throw new RuntimeError('Handler filter only supports AbstractEntity objects.');
    }
}
