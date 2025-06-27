<?php

/**
 * THIS IS A GENERATED FILE, DO NOT EDIT IT.
 * IT WILL BE RECREATED AT EACH NODE-TYPE UPDATE.
 */

declare(strict_types=1);

namespace App\GeneratedEntity\Repository;

use App\GeneratedEntity\NSArticleContainer;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends NodesSourcesRepository<NSArticleContainer>
 * @method NSArticleContainer|null   find($id, $lockMode = null, $lockVersion = null)
 * @method NSArticleContainer|null   findOneBy(array $criteria, array $orderBy = null)
 * @method NSArticleContainer[]      findAll()
 * @method NSArticleContainer[]      findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method NSArticleContainer|null   findOneByIdentifierAndTranslation(string $identifier, ?TranslationInterface $translation, bool $availableTranslation = false)
 * @method NSArticleContainer|null   findOneByNodeAndTranslation(Node $node, ?TranslationInterface $translation)
 * @method NSArticleContainer[]|null findByNodesSourcesAndFieldNameAndTranslation(NodesSources $nodesSources, string $fieldName, array $nodeSourceClasses = [])
 * @method int countBy(mixed $criteria)
 */
final class NSArticleContainerRepository extends NodesSourcesRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
    ) {
        parent::__construct($registry, $previewResolver, $dispatcher, $security, NSArticleContainer::class);
    }
}
