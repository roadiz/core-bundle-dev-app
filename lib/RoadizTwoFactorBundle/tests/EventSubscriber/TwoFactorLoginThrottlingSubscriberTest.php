<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\TwoFactorBundle\EventSubscriber\TwoFactorLoginThrottlingSubscriber;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

final class TwoFactorLoginThrottlingSubscriberTest extends TestCase
{
    private function createSubscriber(int $limit): TwoFactorLoginThrottlingSubscriber
    {
        $factory = new RateLimiterFactory([
            'id' => 'two_factor_login_test',
            'policy' => 'fixed_window',
            'limit' => $limit,
            'interval' => '1 minute',
        ], new InMemoryStorage());

        return new TwoFactorLoginThrottlingSubscriber($factory);
    }

    private function createEvent(string $userIdentifier): TwoFactorAuthenticationEvent
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUserIdentifier')->willReturn($userIdentifier);

        return new TwoFactorAuthenticationEvent(new Request(), $token);
    }

    public function testAttemptsWithinLimitAreAccepted(): void
    {
        $subscriber = $this->createSubscriber(3);
        $event = $this->createEvent('user-one');

        $subscriber->onAttempt($event);
        $subscriber->onAttempt($event);
        $subscriber->onAttempt($event);

        $this->addToAssertionCount(3);
    }

    public function testExceedingLimitThrows(): void
    {
        $subscriber = $this->createSubscriber(2);
        $event = $this->createEvent('user-two');

        $subscriber->onAttempt($event);
        $subscriber->onAttempt($event);

        $this->expectException(TooManyLoginAttemptsAuthenticationException::class);
        $subscriber->onAttempt($event);
    }

    public function testLimitIsPerUserIdentifier(): void
    {
        $subscriber = $this->createSubscriber(1);

        $subscriber->onAttempt($this->createEvent('user-a'));
        // A different user identifier must not be affected by user-a's usage.
        $subscriber->onAttempt($this->createEvent('user-b'));

        $this->addToAssertionCount(2);
    }

    public function testSubscribesToAttemptEventOnly(): void
    {
        $events = TwoFactorLoginThrottlingSubscriber::getSubscribedEvents();

        self::assertArrayHasKey('scheb_two_factor.authentication.attempt', $events);
        self::assertCount(1, $events);
    }
}
