<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TagExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly Tag $tag,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    protected function getEditItemPath(): string
    {
        return $this->urlGenerator->generate('tagsEditPage', [
            'tagId' => $this->tag->getId(),
        ]);
    }

    #[\Override]
    protected function getColor(): string
    {
        return $this->tag->getColor();
    }

    #[\Override]
    public function getId(): int
    {
        return $this->tag->getId() ?? throw new \RuntimeException('Tag ID is null');
    }

    #[\Override]
    public function getAlternativeDisplayable(): string
    {
        return $this->getTagParents($this->tag);
    }

    #[\Override]
    public function getDisplayable(): string
    {
        $firstTrans = $this->tag->getTranslatedTags()->first();
        $name = $this->tag->getTagName();

        if ($firstTrans) {
            $name = $firstTrans->getName();
        }

        return $name;
    }

    #[\Override]
    public function getOriginal(): Tag
    {
        return $this->tag;
    }

    private function getTagParents(Tag $tag, bool $slash = false): string
    {
        $result = '';
        $parent = $tag->getParent();

        if ($parent instanceof Tag) {
            $superParent = $this->getTagParents($parent, true);
            $firstTrans = $parent->getTranslatedTags()->first();
            $name = $parent->getTagName();

            if ($firstTrans) {
                $name = $firstTrans->getName();
            }

            $result = $superParent.$name;

            if ($slash) {
                $result .= ' / ';
            }
        }

        return $result;
    }
}
