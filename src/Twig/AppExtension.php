<?php

declare(strict_types=1);

namespace App\Twig;

use App\TreeWalker\MenuNodeSourceWalker;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Api\Controller\TranslationAwareControllerTrait;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\TreeWalkerGenerator;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class AppExtension extends AbstractExtension implements GlobalsInterface
{
    use TranslationAwareControllerTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly TreeWalkerGenerator $treeWalkerGenerator,
    ) {
    }

    #[\Override]
    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    #[\Override]
    protected function getPreviewResolver(): PreviewResolverInterface
    {
        return $this->previewResolver;
    }

    private function getMenus(): array
    {
        $request = $this->requestStack->getMainRequest();

        return $this->treeWalkerGenerator->getTreeWalkersForTypeAtRoot(
            'Menu',
            MenuNodeSourceWalker::class,
            $this->getTranslation($request),
            3
        );
    }

    #[\Override]
    public function getGlobals(): array
    {
        return [
            'menus' => $this->getMenus(),
        ];
    }
}
