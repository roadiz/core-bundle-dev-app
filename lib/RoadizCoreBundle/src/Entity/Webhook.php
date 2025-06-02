<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\CoreBundle\Repository\WebhookRepository;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: WebhookRepository::class),
    ORM\Table(name: 'webhooks'),
    ORM\Index(columns: ['message_type'], name: 'webhook_message_type'),
    ORM\Index(columns: ['created_at'], name: 'webhook_created_at'),
    ORM\Index(columns: ['updated_at'], name: 'webhook_updated_at'),
    ORM\Index(columns: ['automatic'], name: 'webhook_automatic'),
    ORM\Index(columns: ['root_node'], name: 'webhook_root_node'),
    ORM\Index(columns: ['last_triggered_at'], name: 'webhook_last_triggered_at'),
    ORM\HasLifecycleCallbacks
]
class Webhook extends AbstractDateTimed implements WebhookInterface
{
    #[
        ORM\Id,
        ORM\Column(type: 'string', length: 36),
        SymfonySerializer\Groups(['id']),
    ]
    /** @phpstan-ignore-next-line */
    protected int|string|null $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 250)]
    protected ?string $description = null;

    #[ORM\Column(name: 'message_type', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    protected ?string $messageType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Url]
    protected ?string $uri = null;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $payload = null;

    /**
     * @var int wait between webhook call and webhook triggering request
     */
    #[ORM\Column(name: 'throttleseconds', type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(value: 0)]
    protected int $throttleSeconds = 60;

    #[ORM\Column(name: 'last_triggered_at', type: 'datetime', nullable: true)]
    protected ?\DateTime $lastTriggeredAt = null;

    #[ORM\Column(name: 'automatic', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $automatic = false;

    #[ORM\ManyToOne(targetEntity: Node::class)]
    #[ORM\JoinColumn(name: 'root_node', onDelete: 'SET NULL')]
    #[SymfonySerializer\Ignore]
    protected ?Node $rootNode = null;

    public function __construct(?string $uuid = null)
    {
        $this->id = $uuid ?? \Ramsey\Uuid\Uuid::uuid4()->toString();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Webhook
    {
        $this->description = $description;

        return $this;
    }

    #[\Override]
    public function getMessageType(): ?string
    {
        return $this->messageType;
    }

    public function setMessageType(?string $messageType): Webhook
    {
        $this->messageType = $messageType;

        return $this;
    }

    #[\Override]
    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): Webhook
    {
        $this->uri = $uri;

        return $this;
    }

    #[\Override]
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): Webhook
    {
        $this->payload = $payload;

        return $this;
    }

    #[\Override]
    public function getThrottleSeconds(): int
    {
        return $this->throttleSeconds;
    }

    /**
     * @throws \Exception
     */
    public function getThrottleInterval(): \DateInterval
    {
        return new \DateInterval('PT'.$this->getThrottleSeconds().'S');
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function doNotTriggerBefore(): ?\DateTime
    {
        if (null === $this->getLastTriggeredAt()) {
            return null;
        }
        $doNotTriggerBefore = clone $this->getLastTriggeredAt();

        return $doNotTriggerBefore->add($this->getThrottleInterval());
    }

    public function setThrottleSeconds(int $throttleSeconds): Webhook
    {
        $this->throttleSeconds = $throttleSeconds;

        return $this;
    }

    #[\Override]
    public function getLastTriggeredAt(): ?\DateTime
    {
        return $this->lastTriggeredAt;
    }

    #[\Override]
    public function setLastTriggeredAt(?\DateTime $lastTriggeredAt): Webhook
    {
        $this->lastTriggeredAt = $lastTriggeredAt;

        return $this;
    }

    #[\Override]
    public function isAutomatic(): bool
    {
        return $this->automatic;
    }

    public function setAutomatic(bool $automatic): Webhook
    {
        $this->automatic = $automatic;

        return $this;
    }

    public function getRootNode(): ?Node
    {
        return $this->rootNode;
    }

    public function setRootNode(?Node $rootNode): Webhook
    {
        $this->rootNode = $rootNode;

        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
