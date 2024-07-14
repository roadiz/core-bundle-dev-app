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
 * @extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository<\App\GeneratedEntity\NSPage>
 *
 * @method \App\GeneratedEntity\NSPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\GeneratedEntity\NSPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method \App\GeneratedEntity\NSPage[]    findAll()
 * @method \App\GeneratedEntity\NSPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NSPageRepository extends \RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, $nodeSourceSearchHandler, \App\GeneratedEntity\NSPage::class);
    }
}
