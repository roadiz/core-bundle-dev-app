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
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository<\App\GeneratedEntity\NSArticleFeedBlock>
 *
 * @method \App\GeneratedEntity\NSArticleFeedBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\GeneratedEntity\NSArticleFeedBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method \App\GeneratedEntity\NSArticleFeedBlock[]    findAll()
 * @method \App\GeneratedEntity\NSArticleFeedBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NSArticleFeedBlockRepository extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler);

        $this->_entityName = \App\GeneratedEntity\NSArticleFeedBlock::class;
    }
}
