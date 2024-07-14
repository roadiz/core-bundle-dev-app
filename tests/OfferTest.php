<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\GeneratedEntity\NSOffer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/*
 * This test case requires a running database server and Offer fixtures.
 */
class OfferTest extends ApiTestCase
{
    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->getContainer()->get(ManagerRegistry::class);
    }

    public function testRepository(): void
    {
        $offer = $this->getManagerRegistry()->getRepository(NSOffer::class)->findOneBy([]);
        $this->assertNotNull($offer);
        $this->assertInstanceOf(NSOffer::class, $offer);
    }

    public function testCollection(): void
    {
        $offerCount = $this->getManagerRegistry()->getRepository(NSOffer::class)->countBy([
            'node.nodeType.name' => 'Offer',
        ]);

        static::createClient()->request('GET', '/api/offers');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Offer',
            '@id' => '/api/offers',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => $offerCount,
        ]);
        $this->assertResponseHasHeader('Content-Type');
    }
}
