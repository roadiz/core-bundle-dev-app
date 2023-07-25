<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\CoreBundle\Repository\WebhookRepository;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: WebhookRepository::class),
    ORM\Table(name: "webhooks"),
    ORM\Index(columns: ["message_type"], name: "webhook_message_type"),
    ORM\Index(columns: ["created_at"], name: "webhook_created_at"),
    ORM\Index(columns: ["updated_at"], name: "webhook_updated_at"),
    ORM\Index(columns: ["automatic"], name: "webhook_automatic"),
    ORM\Index(columns: ["root_node"], name: "webhook_root_node"),
    ORM\Index(columns: ["last_triggered_at"], name: "webhook_last_triggered_at"),
    ORM\HasLifecycleCallbacks
]
class Webhook extends AbstractDateTimed implements WebhookInterface
{
    #[
        ORM\Id,
        ORM\Column(type:"string", length:36),
        Serializer\Groups(["id"]),
        SymfonySerializer\Groups(["id"]),
        Serializer\Type("string")
    ]
    /** @phpstan-ignore-next-line */
    protected int|string|null $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 250)]
    #[Serializer\Type('string')]
    protected ?string $description = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'message_type', type: 'string', length: 255, nullable: true)]
    #[Serializer\Type('string')]
    protected ?string $messageType = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Serializer\Type('string')]
    protected ?string $uri = null;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Serializer\Type('array')]
    protected ?array $payload = null;

    /**
     * @var int Wait between webhook call and webhook triggering request.
     */
    #[ORM\Column(name: 'throttleseconds', type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(value: 0)]
    #[Serializer\Type('int')]
    protected int $throttleSeconds = 60;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'last_triggered_at', type: 'datetime', nullable: true)]
    #[Serializer\Type('\DateTime')]
    protected ?\DateTime $lastTriggeredAt = null;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'automatic', type: 'boolean', nullable: false, options: ['default' => false])]
    #[Serializer\Type('boolean')]
    protected bool $automatic = false;

    /**
     * @var Node|null
     */
    #[ORM\ManyToOne(targetEntity: Node::class)]
    #[ORM\JoinColumn(name: 'root_node', onDelete: 'SET NULL')]
    #[SymfonySerializer\Ignore]
    protected ?Node $rootNode = null;

    public function __construct(?string $uuid = null)
    {
        $this->id = $uuid ?? \Ramsey\Uuid\Uuid::uuid4()->toString();
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Webhook
     */
    public function setDescription(?string $description): Webhook
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessageType(): ?string
    {
        return $this->messageType;
    }

    /**
     * @param string|null $messageType
     * @return Webhook
     */
    public function setMessageType(?string $messageType): Webhook
    {
        $this->messageType = $messageType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * @param string|null $uri
     * @return Webhook
     */
    public function setUri(?string $uri): Webhook
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @param array|null $payload
     * @return Webhook
     */
    public function setPayload(?array $payload): Webhook
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return int
     */
    public function getThrottleSeconds(): int
    {
        return $this->throttleSeconds;
    }

    /**
     * @return \DateInterval
     * @throws \Exception
     */
    public function getThrottleInterval(): \DateInterval
    {
        return new \DateInterval('PT' . $this->getThrottleSeconds() . 'S');
    }

    /**
     * @return \DateTime|null
     * @throws \Exception
     */
    public function doNotTriggerBefore(): ?\DateTime
    {
        if (null === $this->getLastTriggeredAt()) {
            return null;
        }
        $doNotTriggerBefore = clone $this->getLastTriggeredAt();
        return $doNotTriggerBefore->add($this->getThrottleInterval());
    }

    /**
     * @param int $throttleSeconds
     * @return Webhook
     */
    public function setThrottleSeconds(int $throttleSeconds): Webhook
    {
        $this->throttleSeconds = $throttleSeconds;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTriggeredAt(): ?\DateTime
    {
        return $this->lastTriggeredAt;
    }

    /**
     * @param \DateTime|null $lastTriggeredAt
     * @return Webhook
     */
    public function setLastTriggeredAt(?\DateTime $lastTriggeredAt): Webhook
    {
        $this->lastTriggeredAt = $lastTriggeredAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAutomatic(): bool
    {
        return $this->automatic;
    }

    /**
     * @param bool $automatic
     * @return Webhook
     */
    public function setAutomatic(bool $automatic): Webhook
    {
        $this->automatic = $automatic;
        return $this;
    }

    /**
     * @return Node|null
     */
    public function getRootNode(): ?Node
    {
        return $this->rootNode;
    }

    /**
     * @param Node|null $rootNode
     * @return Webhook
     */
    public function setRootNode(?Node $rootNode): Webhook
    {
        $this->rootNode = $rootNode;
        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
