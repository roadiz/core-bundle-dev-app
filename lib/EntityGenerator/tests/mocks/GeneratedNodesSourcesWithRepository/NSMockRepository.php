<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace tests\mocks\GeneratedNodesSourcesWithRepository\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository<\tests\mocks\GeneratedNodesSourcesWithRepository\NSMock>
 *
 * @method \tests\mocks\GeneratedNodesSourcesWithRepository\NSMock|null find($id, $lockMode = null, $lockVersion = null)
 * @method \tests\mocks\GeneratedNodesSourcesWithRepository\NSMock|null findOneBy(array $criteria, array $orderBy = null)
 * @method \tests\mocks\GeneratedNodesSourcesWithRepository\NSMock[]    findAll()
 * @method \tests\mocks\GeneratedNodesSourcesWithRepository\NSMock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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

        $this->_entityName = \tests\mocks\GeneratedNodesSourcesWithRepository\NSMock::class;
    }
}
