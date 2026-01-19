<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\NodeVoter;
use RZ\Roadiz\CoreBundle\TwigExtension\JwtExtension;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class NodesSourcesRedirectController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
        private ?string $customPublicScheme,
        private ?string $customPreviewScheme,
        private JwtExtension $jwtExtension,
    ) {
    }

    #[Route(
        path: '/rz-admin/nodes/source/public-redirect/{nodeSourceId}',
        name: 'nodesSourcesPublicRedirect',
        requirements: ['nodeSourceId' => '[0-9]+'],
        methods: ['GET'],
    )]
    public function redirectToPublicPage(
        #[MapEntity(expr: 'repository.find(nodeSourceId)')]
        NodesSources $nodesSources,
    ): RedirectResponse {
        if (!$this->security->isGranted(NodeVoter::EDIT_CONTENT, $nodesSources)) {
            throw new AccessDeniedException();
        }

        if (!$nodesSources->isReachable()) {
            throw new NotFoundHttpException();
        }

        $publicUrl = $this->urlGenerator->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            array_filter([
                RouteObjectInterface::ROUTE_OBJECT => $nodesSources,
                'canonicalScheme' => $this->customPublicScheme,
                '_no_cache' => 1,
            ]),
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new RedirectResponse($publicUrl);
    }

    #[Route(
        path: '/rz-admin/nodes/source/preview-redirect/{nodeSourceId}',
        name: 'nodesSourcesPreviewRedirect',
        requirements: ['nodeSourceId' => '[0-9]+'],
        methods: ['GET'],
    )]
    public function redirectToPreviewPage(
        #[MapEntity(expr: 'repository.find(nodeSourceId)')]
        NodesSources $nodesSources,
    ): RedirectResponse {
        if (!$this->security->isGranted(NodeVoter::EDIT_CONTENT, $nodesSources)) {
            throw new AccessDeniedException();
        }

        if (!$nodesSources->isReachable()) {
            throw new NotFoundHttpException();
        }

        $publicUrl = $this->urlGenerator->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            array_filter([
                RouteObjectInterface::ROUTE_OBJECT => $nodesSources,
                'canonicalScheme' => $this->customPreviewScheme ?? $this->customPublicScheme,
                'token' => $this->jwtExtension->createPreviewJwt(),
                '_preview' => 1,
                '_no_cache' => 1,
            ]),
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new RedirectResponse($publicUrl);
    }
}
