<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\GeneratedEntity\NSOffer;
use App\GeneratedEntity\Repository\NSOfferRepository;
use Doctrine\DBAL\Exception;

/*
 * This test case requires a running database server and Offer fixtures.
 */
class OfferTest extends ApiTestCase
{
    public function testRepository(): void
    {
        try {
            $offer = static::getContainer()->get(NSOfferRepository::class)->findOneBy([]);
            $this->assertNotNull($offer);
            $this->assertInstanceOf(NSOffer::class, $offer);
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error.');
        }
    }

    public function testCollection(): void
    {
        try {
            $offerCount = static::getContainer()->get(NSOfferRepository::class)->countBy([
                'node.nodeTypeName' => 'Offer',
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
