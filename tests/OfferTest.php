<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\GeneratedEntity\NSOffer;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

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
        try {
            $offer = $this->getManagerRegistry()->getRepository(NSOffer::class)->findOneBy([]);
            $this->assertNotNull($offer);
            $this->assertInstanceOf(NSOffer::class, $offer);
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error.');
        }
    }

    public function testCollection(): void
    {
        try {
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
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error.');
        }
    }
}
