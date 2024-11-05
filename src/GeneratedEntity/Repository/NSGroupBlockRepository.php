<?php

/**
 * THIS IS A GENERATED FILE, DO NOT EDIT IT.
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.
 */

declare(strict_types=1);

namespace App\GeneratedEntity\Repository;

use App\GeneratedEntity\NSGroupBlock;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends NodesSourcesRepository<NSGroupBlock>
 *
 * @method NSGroupBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method NSGroupBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method NSGroupBlock[]    findAll()
 * @method NSGroupBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class NSGroupBlockRepository extends NodesSourcesRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler, NSGroupBlock::class);
    }
}
