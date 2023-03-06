<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\User;

use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class PreviewUserProvider implements PreviewUserProviderInterface
{
    private PreviewResolverInterface $previewResolver;
    private Security $security;

    /**
     * @param PreviewResolverInterface $previewResolver
     * @param Security $security
     */
    public function __construct(PreviewResolverInterface $previewResolver, Security $security)
    {
        $this->previewResolver = $previewResolver;
        $this->security = $security;
    }

    public function createFromSecurity(): UserInterface
    {
        if (!$this->security->isGranted($this->previewResolver->getRequiredRole())) {
            throw new AccessDeniedException(
                'Cannot create a preview user proxy from a user that is not allowed to preview.'
            );
        }
        return new PreviewUser($this->security->getUser()->getUserIdentifier(), [
            $this->previewResolver->getRequiredRole()
        ]);
    }
}
