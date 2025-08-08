<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class LogTrail
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Publish a confirmation message in Session flash bag and
     * logger interface.
     */
    public function publishConfirmMessage(?Request $request, string $msg, ?object $source = null): void
    {
        $this->publishMessage($request, $msg, 'confirm', $source);
    }

    /**
     * Publish an error message in Session flash bag and
     * logger interface.
     */
    public function publishErrorMessage(?Request $request, string $msg, ?object $source = null): void
    {
        $this->publishMessage($request, $msg, 'error', $source);
    }

    /**
     * Publish a message in Session flash bag and
     * logger interface.
     */
    protected function publishMessage(
        ?Request $request,
        string $msg,
        string $level = 'confirm',
        ?object $source = null,
    ): void {
        $session = $this->getSession($request);
        if ($session instanceof Session) {
            $session->getFlashBag()->add($level, $msg);
        }

        match ($level) {
            'error', 'danger', 'fail' => $this->logger->error($msg, ['entity' => $source]),
            default => $this->logger->info($msg, ['entity' => $source]),
        };
    }

    /**
     * Returns the current session.
     */
    public function getSession(?Request $request): ?SessionInterface
    {
        return !$request?->hasPreviousSession() ? null : $request->getSession();
    }
}
