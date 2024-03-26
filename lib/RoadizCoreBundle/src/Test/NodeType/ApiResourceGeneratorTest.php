<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Test\NodeType;

use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponse;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\NodeType\ApiResourceGenerator;
use RZ\Roadiz\CoreBundle\NodeType\ApiResourceOperationNameGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class ApiResourceGeneratorTest extends KernelTestCase
{
    protected static function getGeneratedPath(): string
    {
        return dirname(__DIR__) . '/../../tests/generated_api_resources';
    }

    protected function getApiResourceGenerator(): ApiResourceGenerator
    {
        /** @var ApiResourceOperationNameGenerator $apiResourceOperationNameGenerator */
        $apiResourceOperationNameGenerator = $this->getContainer()->get(ApiResourceOperationNameGenerator::class);

        return new ApiResourceGenerator(
            $apiResourceOperationNameGenerator,
            static::getGeneratedPath(),
            new NullLogger(),
            WebResponse::class
        );
    }

    public function testGenerate(): void
    {
        $apiResourceGenerator = $this->getApiResourceGenerator();

        $nodeType = new NodeType();
        $nodeType->setName('Test');

        $apiResourceGenerator->generate($nodeType);
        $resourcePath = $apiResourceGenerator->getResourcePath($nodeType);
        $this->assertFileExists($resourcePath);
        $this->assertFileEquals(
            dirname(__DIR__) . '/../../tests/expected_api_resources/nstest.yml',
            $resourcePath
        );
    }

    public function testReachableGenerate(): void
    {
        $apiResourceGenerator = $this->getApiResourceGenerator();

        $nodeType = new NodeType();
        $nodeType->setName('Test');
        $nodeType->setReachable(true);

        $apiResourceGenerator->generate($nodeType);
        $resourcePath = $apiResourceGenerator->getResourcePath($nodeType);
        $this->assertFileExists($resourcePath);
        $this->assertFileExists(dirname(__DIR__) . '/../../tests/generated_api_resources/web_response.yml');
        $this->assertFileEquals(
            dirname(__DIR__) . '/../../tests/expected_api_resources/nstest.yml',
            $resourcePath
        );
        $this->assertFileEquals(
            dirname(__DIR__) . '/../../tests/expected_api_resources/web_response.yml',
            dirname(__DIR__) . '/../../tests/generated_api_resources/web_response.yml',
        );
    }

    public function testMultipleGenerate(): void
    {
        $apiResourceGenerator = $this->getApiResourceGenerator();

        $nodeType = new NodeType();
        $nodeType->setName('Test');
        $nodeType->setReachable(true);

        $nodeType2 = new NodeType();
        $nodeType2->setName('SecondTest');
        $nodeType2->setReachable(true);

        $apiResourceGenerator->generate($nodeType);
        $resourcePath = $apiResourceGenerator->getResourcePath($nodeType);
        $this->assertFileExists($resourcePath);
        $this->assertFileEquals(
            dirname(__DIR__) . '/../../tests/expected_api_resources/nstest.yml',
            $resourcePath
        );

        $apiResourceGenerator->generate($nodeType2);
        $resourcePath2 = $apiResourceGenerator->getResourcePath($nodeType2);
        $this->assertFileExists($resourcePath2);
        $this->assertFileEquals(
            dirname(__DIR__) . '/../../tests/expected_api_resources/nssecondtest.yml',
            $resourcePath2
        );

        $this->assertFileExists(dirname(__DIR__) . '/../../tests/generated_api_resources/web_response.yml');
        $this->assertFileEquals(
            dirname(__DIR__) . '/../../tests/expected_api_resources/web_response_multiple.yml',
            dirname(__DIR__) . '/../../tests/generated_api_resources/web_response.yml',
        );
    }

    public function testRemoveGenerate(): void
    {
        $apiResourceGenerator = $this->getApiResourceGenerator();

        $nodeType = new NodeType();
        $nodeType->setName('Test');
        $nodeType->setReachable(true);

        $nodeType2 = new NodeType();
        $nodeType2->setName('SecondTest');
        $nodeType2->setReachable(true);

        $apiResourceGenerator->generate($nodeType);
        $apiResourceGenerator->generate($nodeType2);

        // Remove second node-type to check if operation
        // is removed from web_response
        $apiResourceGenerator->remove($nodeType2);
        $resourcePath2 = $apiResourceGenerator->getResourcePath($nodeType2);
        $this->assertFileDoesNotExist($resourcePath2);

        $this->assertFileExists(dirname(__DIR__) . '/../../tests/generated_api_resources/web_response.yml');
        $this->assertFileEquals(
            dirname(__DIR__) . '/../../tests/expected_api_resources/web_response.yml',
            dirname(__DIR__) . '/../../tests/generated_api_resources/web_response.yml',
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $filesystem = new Filesystem();
        $filesystem->mkdir(static::getGeneratedPath());
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        $filesystem = new Filesystem();
        $filesystem->remove(static::getGeneratedPath());
    }
}
