<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\Model;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHead;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class NodesSourcesHeadTest extends TestCase
{
    /**
     * Build a node-source double reproducing the getMetaDescriptionOrFallback()
     * override that EntityGenerator emits for a flagged node-type.
     */
    private function nodeSource(
        string $metaDescription = '',
        ?string $fallback = null,
        ?string $title = null,
        ?string $metaTitle = null,
    ): NodesSources {
        $nodeSource = new class extends NodesSources {
            public ?string $fallbackValue = null;

            public function __construct()
            {
            }

            #[\Override]
            public function getMetaDescriptionOrFallback(): string
            {
                $metaDescription = $this->getMetaDescription();
                if ('' !== $metaDescription) {
                    return $metaDescription;
                }

                return (string) ($this->fallbackValue ?? '');
            }
        };
        $nodeSource->fallbackValue = $fallback;
        $nodeSource->setMetaDescription($metaDescription);
        if (null !== $title) {
            $nodeSource->setTitle($title);
        }
        if (null !== $metaTitle) {
            $nodeSource->setMetaTitle($metaTitle);
        }

        return $nodeSource;
    }

    private function createHead(?NodesSources $nodeSource): NodesSourcesHead
    {
        // An empty settings bag is enough: the tested branch does not rely on
        // any setting but "site_name", which we assert around explicitly.
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getRepository')->willThrowException(new \RuntimeException('no database in unit test'));
        $settings = new Settings($managerRegistry, new Stopwatch());

        // Identity strip keeps assertions about length/truncation deterministic;
        // the real stripping behaviour is covered by CommonMarkTest.
        $markdown = $this->createStub(MarkdownInterface::class);
        $markdown->method('strip')->willReturnArgument(0);

        return new NodesSourcesHead(
            $nodeSource,
            $settings,
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(HandlerFactoryInterface::class),
            $this->createStub(TranslationInterface::class),
            $markdown,
        );
    }

    public function testEligibleMetaDescriptionIsExposed(): void
    {
        $head = $this->createHead($this->nodeSource(
            metaDescription: 'This description is definitely long enough.',
        ));

        $this->assertSame('This description is definitely long enough.', $head->getMetaDescription());
    }

    public function testTooShortMetaDescriptionIsDiscarded(): void
    {
        $head = $this->createHead($this->nodeSource(metaDescription: 'Too short'));

        $this->assertNull($head->getMetaDescription());
    }

    public function testMetaDescriptionExactlyAtThresholdIsDiscarded(): void
    {
        // 20 characters: the eligibility test requires *more* than 20.
        $head = $this->createHead($this->nodeSource(metaDescription: str_repeat('a', 20)));

        $this->assertNull($head->getMetaDescription());
    }

    public function testFallbackIsUsedWhenMetaDescriptionIsEmpty(): void
    {
        $head = $this->createHead($this->nodeSource(
            metaDescription: '',
            fallback: 'A long enough fallback taken from the content field.',
        ));

        $this->assertSame('A long enough fallback taken from the content field.', $head->getMetaDescription());
    }

    public function testTooShortFallbackIsDiscarded(): void
    {
        $head = $this->createHead($this->nodeSource(metaDescription: '', fallback: 'Short one'));

        $this->assertNull($head->getMetaDescription());
    }

    public function testLongDescriptionIsTruncatedTo160Characters(): void
    {
        $head = $this->createHead($this->nodeSource(
            metaDescription: str_repeat('Roadiz content ', 20),
        ));

        $description = $head->getMetaDescription();

        $this->assertNotNull($description);
        // truncate(160, '…', cut: false) completes the last word, so the result
        // hovers around — and may slightly exceed — 160 characters, but must be
        // far shorter than the 300-character source and carry the ellipsis.
        $this->assertLessThan(200, mb_strlen($description));
        $this->assertStringEndsWith('…', $description);
        $this->assertStringStartsWith('Roadiz content', $description);
    }

    public function testMetaTitleTakesPrecedenceOverComputedTitle(): void
    {
        $head = $this->createHead($this->nodeSource(
            title: 'Node title',
            metaTitle: 'Custom SEO title',
        ));

        $this->assertSame('Custom SEO title', $head->getMetaTitle());
    }

    public function testTitleFallsBackToNodeTitle(): void
    {
        $head = $this->createHead($this->nodeSource(title: 'My node'));

        $this->assertStringStartsWith('My node – ', (string) $head->getMetaTitle());
    }
}
