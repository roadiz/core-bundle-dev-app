<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter
 */
class BNodesFilter extends ANodesFilter
{
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', 40]],
            QueryBuilderBuildEvent::class => [['onNodeQueryBuilderBuild', 30]]
        ];
    }

    /**
     * @return string
     */
    protected function getProperty(): string
    {
        return 'bNodes';
    }

    /**
     * @return string
     */
    protected function getNodeJoinAlias(): string
    {
        return 'b_n';
    }
}
