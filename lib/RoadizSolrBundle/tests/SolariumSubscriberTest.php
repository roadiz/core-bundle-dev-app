<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent;
use RZ\Roadiz\SolrBundle\EventListener\SolariumSubscriber;
use RZ\Roadiz\SolrBundle\Message\SolrReindexMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class SolariumSubscriberTest extends TestCase
{
    /**
     * @var list<object>
     */
    private array $sent = [];

    private function createSubscriber(): SolariumSubscriber
    {
        $this->sent = [];
        $bus = new class($this->sent) implements MessageBusInterface {
            /**
             * @param list<object> $sent
             */
            public function __construct(private array &$sent)
            {
            }

            #[\Override]
            public function dispatch(object $message, array $stamps = []): Envelope
            {
                $envelope = Envelope::wrap($message, $stamps);
                $this->sent[] = $envelope->getMessage();

                return $envelope;
            }
        };

        return new SolariumSubscriber($bus);
    }

    private function postFlushArgs(): PostFlushEventArgs
    {
        return new PostFlushEventArgs($this->createMock(EntityManagerInterface::class));
    }

    private function nodeUpdated(int $id): NodeUpdatedEvent
    {
        $node = $this->createMock(Node::class);
        $node->method('getId')->willReturn($id);

        return new NodeUpdatedEvent($node);
    }

    /**
     * Rozier dispatches its node events before flushing, so sending the indexing
     * message right away races the commit: the worker re-reads the node by id and
     * can index the pre-flush state (a just-published source staying `node_status_i:10`
     * in Solr, hence invisible to search).
     */
    public function testIndexingMessageIsHeldBackUntilTheOrmHasCommitted(): void
    {
        $subscriber = $this->createSubscriber();

        $subscriber->onSolariumNodeUpdate($this->nodeUpdated(357));
        $this->assertSame([], $this->sent, 'Nothing may reach the bus before the flush.');

        $subscriber->postFlush($this->postFlushArgs());

        $this->assertCount(1, $this->sent);
        $this->assertInstanceOf(SolrReindexMessage::class, $this->sent[0]);
        $this->assertSame(357, $this->sent[0]->getIdentifier());
    }

    /**
     * TranslateNodeMessageHandler flushes *then* dispatches: no postFlush will ever
     * follow, so the end of the request/worker message has to drain the buffer too.
     */
    public function testBufferIsAlsoDrainedWhenNoFlushFollows(): void
    {
        $subscriber = $this->createSubscriber();

        $subscriber->onSolariumNodeUpdate($this->nodeUpdated(357));
        $subscriber->send();

        $this->assertCount(1, $this->sent);
    }

    /**
     * A worker runtime reuses this instance across requests: a buffer left behind
     * by a request that died must not be sent on behalf of the next one.
     */
    public function testResetDropsWhatNoDrainClaimed(): void
    {
        $subscriber = $this->createSubscriber();

        $subscriber->onSolariumNodeUpdate($this->nodeUpdated(357));
        $subscriber->reset();
        $subscriber->send();

        $this->assertSame([], $this->sent);
    }

    public function testDrainedMessagesAreSentOnlyOnce(): void
    {
        $subscriber = $this->createSubscriber();

        $subscriber->onSolariumNodeUpdate($this->nodeUpdated(357));
        $subscriber->postFlush($this->postFlushArgs());
        $subscriber->postFlush($this->postFlushArgs());
        $subscriber->send();

        $this->assertCount(1, $this->sent);
    }
}
