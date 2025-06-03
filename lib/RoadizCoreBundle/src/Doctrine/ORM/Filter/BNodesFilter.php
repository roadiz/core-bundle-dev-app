<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;

class BNodesFilter extends ANodesFilter
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', 40]],
            QueryBuilderBuildEvent::class => [['onNodeQueryBuilderBuild', 30]],
        ];
    }

    #[\Override]
    protected function getProperty(): string
    {
        return 'bNodes';
    }

    #[\Override]
    protected function getNodeJoinAlias(): string
    {
        return 'b_n';
    }
}
