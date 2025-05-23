<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\GeneratedEntity\NSArticle;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/*
 * This test case requires a running database server and Article fixtures.
 */
class ArticleTest extends ApiTestCase
{
    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->getContainer()->get(ManagerRegistry::class);
    }

    public function testRepository(): void
    {
        try {
            $article = $this->getManagerRegistry()->getRepository(NSArticle::class)->findOneBy([]);
            $this->assertNotNull($article);
            $this->assertInstanceOf(NSArticle::class, $article);
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error.');
        }
    }

    public function testCollection(): void
    {
        try {
            $articleCount = $this->getManagerRegistry()->getRepository(NSArticle::class)->countBy([]);

            static::createClient()->request('GET', '/api/articles');

            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([
                '@context' => '/api/contexts/Article',
                '@id' => '/api/articles',
                '@type' => 'hydra:Collection',
                'hydra:totalItems' => $articleCount,
            ]);
            $this->assertResponseHasHeader('Content-Type');
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error.');
        }
    }

    public function testSingleArticle(): void
    {
        try {
            $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);
            $article = $this->getManagerRegistry()->getRepository(NSArticle::class)->findOneBy([]);
            if (null === $article) {
                $this->fail('No article found in database.');
            }

            $this->assertInstanceOf(NSArticle::class, $article);

            static::createClient()->request('GET', '/api/articles/'.$article->getId());

            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([
                '@context' => '/api/contexts/Article',
                '@id' => '/api/articles/'.$article->getId(),
                '@type' => 'Article',
                'title' => $article->getTitle(),
                'url' => $urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                    RouteObjectInterface::ROUTE_OBJECT => $article,
                ]),
            ]);
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error.');
        }
    }

    public function testArticleWebResponse(): void
    {
        try {
            $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);
            $article = $this->getManagerRegistry()->getRepository(NSArticle::class)->findOneBy([]);
            if (null === $article) {
                $this->fail('No article found in database.');
            }
            $this->assertInstanceOf(NSArticle::class, $article);

            $path = $urlGenerator->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
                RouteObjectInterface::ROUTE_OBJECT => $article,
            ]);

            static::createClient()->request('GET', '/api/web_response_by_path', [
                'query' => [
                    'path' => $path,
                ],
            ]);

            $this->assertResponseIsSuccessful();
            $this->assertJsonContains([
                '@context' => '/api/contexts/WebResponse',
                '@id' => '/api/web_response_by_path',
                '@type' => 'WebResponse',
            ]);
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error.');
        }
    }
}
