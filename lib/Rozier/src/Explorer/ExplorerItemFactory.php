<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemFactoryInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Renderer\RendererInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ExplorerItemFactory implements ExplorerItemFactoryInterface
{
    public function __construct(
        private readonly RendererInterface $renderer,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EmbedFinderFactory $embedFinderFactory,
        private readonly Security $security,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function createForEntity(mixed $entity, array $configuration = []): ExplorerItemInterface
    {
        return match (true) {
            $entity instanceof DocumentInterface => new DocumentExplorerItem(
                $entity,
                $this->renderer,
                $this->documentUrlGenerator,
                $this->urlGenerator,
                $this->embedFinderFactory
            ),
            $entity instanceof NodeType => new NodeTypeExplorerItem(
                $entity,
                $this->urlGenerator,
            ),
            $entity instanceof Folder => new FolderExplorerItem(
                $entity,
                $this->urlGenerator,
            ),
            $entity instanceof Setting => new SettingExplorerItem(
                $entity,
                $this->urlGenerator,
            ),
            $entity instanceof User => new UserExplorerItem(
                $entity,
                $this->urlGenerator,
            ),
            $entity instanceof Node => new NodeExplorerItem(
                $entity,
                $this->urlGenerator,
                $this->security
            ),
            $entity instanceof NodesSources => new NodeSourceExplorerItem(
                $entity,
                $this->urlGenerator,
                $this->security
            ),
            $entity instanceof Tag => new TagExplorerItem(
                $entity,
                $this->urlGenerator,
            ),
            $entity instanceof CustomForm => new CustomFormExplorerItem(
                $entity,
                $this->urlGenerator,
                $this->translator,
            ),
            default => new ConfigurableExplorerItem($entity, $configuration),
        };
    }
}
