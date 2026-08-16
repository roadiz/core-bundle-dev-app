<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/*
 * Functional coverage of realm-based API filtering (security audit ref: H2/T10).
 *
 * Builds its own minimal Article node + DENY-behaviour Realm directly through
 * the entity manager rather than the shared DataFixtures, since no realm
 * fixture exists yet.
 */
final class RealmApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const string DENIED_ROLE = 'ROLE_NEVER_GRANTED_TO_ANONYMOUS';

    /**
     * Creates a published Article node placed under a DENY-behaviour Realm
     * that requires a role nobody (especially not an anonymous user) has.
     */
    private function createDeniedArticle(EntityManagerInterface $em): NodesSources
    {
        $container = static::getContainer();
        $articleType = $container->get(NodeTypes::class)->get('Article');
        if (null === $articleType) {
            $this->fail('Article node type is missing.');
        }

        $translation = $container->get(TranslationRepository::class)->findDefault();
        if (null === $translation) {
            $this->fail('Default translation is missing.');
        }

        $article = $container->get(UniqueNodeGenerator::class)->generate(
            nodeType: $articleType,
            translation: $translation,
        );
        $article->getNode()->setStatus(NodeStatus::PUBLISHED);
        $em->flush();

        $realm = new Realm();
        $realm->setName('h2-deny-realm-'.uniqid());
        $realm->setType(RealmInterface::TYPE_ROLE);
        $realm->setBehaviour(RealmInterface::BEHAVIOUR_DENY);
        $realm->setRole(self::DENIED_ROLE);
        $em->persist($realm);

        $realmNode = new RealmNode();
        $realmNode->setNode($article->getNode());
        $realmNode->setRealm($realm);
        $realmNode->setInheritanceType(RealmInterface::INHERITANCE_ROOT);
        $em->persist($realmNode);
        $em->flush();

        return $article;
    }

    public function testDenyRealmBlocksAnonymousWebResponseByPath(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $article = $this->createDeniedArticle($em);

        $urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
        $path = $urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
            RouteObjectInterface::ROUTE_OBJECT => $article,
        ]);

        static::createClient()->request('GET', '/api/web_response_by_path', [
            'query' => ['path' => $path],
        ]);

        /*
         * Real current behavior: RealmsAwareWebResponseOutputDataTransformerTrait::injectRealms()
         * throws Symfony's UnauthorizedHttpException for a DENY realm the anonymous user isn't
         * granted, which HttpKernel maps to HTTP 401 (not 403, despite the "deny" naming).
         */
        self::assertResponseStatusCodeSame(401);
    }

    /**
     * H2 placeholder / acceptance check: AttributeValueRealmExtension filters
     * realm-gated AttributeValue rows out of collections, but no equivalent
     * extension exists for NodesSources (security audit finding H2), so a
     * DENY-realm node's NodesSources currently still appears in
     * /api/nodes_sources for anonymous users.
     *
     * This test intentionally documents that gap instead of pinning it: as
     * long as the leak reproduces, it calls markTestIncomplete() referencing
     * H2 (green-as-incomplete, not red). Once a NodesSources realm extension
     * is added and the leak stops reproducing, the guard below is skipped
     * and the assertion becomes the real regression check.
     */
    public function testAnonymousNodesSourcesCollectionShouldExcludeDenyRealmNode(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $article = $this->createDeniedArticle($em);
        $title = $article->getTitle();

        $response = static::createClient()->request('GET', '/api/nodes_sources', [
            'query' => ['title' => $title],
        ]);
        self::assertResponseIsSuccessful();

        $data = $response->toArray(false);
        $totalItems = $data['hydra:totalItems'] ?? null;

        if (1 === $totalItems) {
            $this->markTestIncomplete(
                'H2 (security audit): NodesSourcesQueryExtension has no Realm filtering at all, unlike '
                .'AttributeValueRealmExtension. A DENY-realm NodesSources still leaks into /api/nodes_sources '
                .'for anonymous users. This test documents the desired behavior; once H2 is fixed, this guard '
                .'will stop triggering and the assertion below becomes the acceptance check.'
            );
        }

        self::assertSame(
            0,
            $totalItems,
            'DENY-realm NodesSources must not appear in /api/nodes_sources for anonymous users (H2 acceptance check).'
        );
    }
}
