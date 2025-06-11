<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\Model\CommonContent;
use App\TreeWalker\MenuNodeSourceWalker;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Controller\TranslationAwareControllerTrait;
use RZ\Roadiz\CoreBundle\Api\Model\NodesSourcesHeadFactoryInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\TreeWalkerGenerator;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final class GetCommonContentController extends AbstractController
{
    use TranslationAwareControllerTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
        private readonly NodesSourcesHeadFactoryInterface $nodesSourcesHeadFactory,
        private readonly PreviewResolverInterface $previewResolver,
        private readonly TreeWalkerGenerator $treeWalkerGenerator,
        private readonly Settings $settingsBag,
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

    public function __invoke(): ?CommonContent
    {
        try {
            $request = $this->requestStack->getMainRequest();
            $translation = $this->getTranslation($request);

            $resource = new CommonContent();

            $request?->attributes->set('data', $resource);
            $resource->home = $this->getHomePage($translation);
            $resource->head = $this->nodesSourcesHeadFactory->createForTranslation($translation);
            $resource->menus = $this->treeWalkerGenerator->getTreeWalkersForTypeAtRoot(
                'Menu',
                MenuNodeSourceWalker::class,
                $translation,
                3
            );

            /*
             * Autoprovide all _url and _color settings. Make sure to not create private settings using these keys.
             */
            $urlKeys = array_filter(
                $this->settingsBag->keys(),
                fn (string $key) => str_ends_with($key, '_url') && !empty($this->settingsBag->get($key)),
            );
            $resource->urls = [];
            foreach ($urlKeys as $urlKey) {
                $resource->urls[$urlKey] = $this->settingsBag->get($urlKey);
            }

            $colorKeys = array_filter(
                $this->settingsBag->keys(),
                fn (string $key) => str_ends_with($key, '_color') && !empty($this->settingsBag->get($key)),
            );
            $resource->colors = [];
            foreach ($colorKeys as $colorKey) {
                $resource->colors[$colorKey] = $this->settingsBag->get($colorKey);
            }

            return $resource;
        } catch (ResourceNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }
    }

    protected function getHomePage(TranslationInterface $translation): ?NodesSources
    {
        return $this->managerRegistry->getRepository(NodesSources::class)->findOneBy([
            'node.home' => true,
            'translation' => $translation,
        ]);
    }

    protected function getTranslationRepository(): TranslationRepository
    {
        $repository = $this->managerRegistry->getRepository(TranslationInterface::class);
        if (!$repository instanceof TranslationRepository) {
            throw new \RuntimeException('Translation repository must be instance of '.TranslationRepository::class);
        }

        return $repository;
    }
}
