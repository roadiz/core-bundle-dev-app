<?php

/**
 * THIS IS A GENERATED FILE, DO NOT EDIT IT.
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.
 */

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\NSMock;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends NodesSourcesRepository<NSMock>
 * @method NSMock|null find($id, $lockMode = null, $lockVersion = null)
 * @method NSMock|null findOneBy(array $criteria, array $orderBy = null)
 * @method NSMock[]    findAll()
 * @method NSMock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NSMockRepository extends NodesSourcesRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler, NSMock::class);
    }
}
