<?php

declare(strict_types=1);

/*
 * THIS IS A GENERATED FILE, DO NOT EDIT IT
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE
 */
namespace App\GeneratedEntity\Repository;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository<\App\GeneratedEntity\NSGroupBlock>
 *
 * @method \App\GeneratedEntity\NSGroupBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\GeneratedEntity\NSGroupBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method \App\GeneratedEntity\NSGroupBlock[]    findAll()
 * @method \App\GeneratedEntity\NSGroupBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NSGroupBlockRepository extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler);

        $this->_entityName = \App\GeneratedEntity\NSGroupBlock::class;
    }
}
