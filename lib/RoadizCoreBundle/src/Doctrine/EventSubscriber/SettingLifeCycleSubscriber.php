<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\HiddenString\HiddenString;
use Psr\Log\LoggerInterface;
use RZ\Crypto\Encoder\UniqueKeyEncoderInterface;
use RZ\Roadiz\CoreBundle\Crypto\UniqueKeyEncoderFactory;
use RZ\Roadiz\CoreBundle\Entity\Setting;

final class SettingLifeCycleSubscriber implements EventSubscriber
{
    private UniqueKeyEncoderFactory $uniqueKeyEncoderFactory;
    private string $privateKeyName;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, UniqueKeyEncoderFactory $uniqueKeyEncoderFactory, string $privateKeyName)
    {
        $this->uniqueKeyEncoderFactory = $uniqueKeyEncoderFactory;
        $this->privateKeyName = $privateKeyName;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::postLoad
        ];
    }

    /**
     * @param PreUpdateEventArgs $event
     * @throws InvalidKey
     */
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $setting = $event->getObject();
        if ($setting instanceof Setting) {
            if (
                $event->hasChangedField('encrypted') &&
                $event->getNewValue('encrypted') === false &&
                null !== $setting->getRawValue()
            ) {
                /*
                 * Set raw value and do not encode it if setting is not encrypted anymore.
                 */
                $setting->setValue($setting->getRawValue());
            } elseif (
                $event->hasChangedField('encrypted') &&
                $event->getNewValue('encrypted') === true &&
                null !== $setting->getRawValue()
            ) {
                /*
                 * Encode value for the first time.
                 */
                $setting->setValue($this->getEncoder()->encode(new HiddenString($setting->getRawValue())));
            } elseif (
                $setting->isEncrypted() &&
                $event->hasChangedField('value') &&
                null !== $event->getNewValue('value')
            ) {
                /*
                 * Encode setting if value has changed
                 */
                $event->setNewValue('value', $this->getEncoder()->encode(new HiddenString($event->getNewValue('value'))));
                $setting->setClearValue($event->getNewValue('value'));
            }
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event): void
    {
        $setting = $event->getObject();
        if (
            $setting instanceof Setting &&
            $setting->isEncrypted() &&
            null !== $setting->getRawValue()
        ) {
            try {
                $setting->setClearValue($this->getEncoder()->decode($setting->getRawValue())->getString());
            } catch (InvalidKey $exception) {
                $this->logger->error(
                    sprintf('Failed to decode "%s" setting value', $setting->getName()),
                    [
                        'exception_message' => $exception->getMessage(),
                        'trace' => $exception->getTraceAsString(),
                        'entity' => $setting
                    ]
                );
            } catch (InvalidMessage $exception) {
                $this->logger->error(
                    sprintf('Failed to decode "%s" setting value', $setting->getName()),
                    [
                        'exception_message' => $exception->getMessage(),
                        'trace' => $exception->getTraceAsString(),
                        'entity' => $setting
                    ]
                );
            }
        }
    }

    /**
     * @throws InvalidKey
     */
    protected function getEncoder(): UniqueKeyEncoderInterface
    {
        return $this->uniqueKeyEncoderFactory->getEncoder($this->privateKeyName);
    }
}
