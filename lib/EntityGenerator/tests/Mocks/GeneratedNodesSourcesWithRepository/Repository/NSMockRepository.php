<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository<\RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\NSMock>
 *
 * @method \RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\NSMock|null find($id, $lockMode = null, $lockVersion = null)
 * @method \RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\NSMock|null findOneBy(array $criteria, array $orderBy = null)
 * @method \RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\NSMock[]    findAll()
 * @method \RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\NSMock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NSMockRepository extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler);

        $this->_entityName = \RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSourcesWithRepository\NSMock::class;
    }
}
