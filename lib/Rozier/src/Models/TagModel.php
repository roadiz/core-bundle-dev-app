<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TagModel extends AbstractExplorerItem
{
    public function __construct(
        private readonly Tag $tag,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('tagsEditPage', [
            'tagId' => $this->tag->getId()
        ]);
    }

    protected function getColor(): ?string
    {
        return $this->tag->getColor();
    }

    public function getId(): string|int
    {
        return $this->tag->getId();
    }

    public function getAlternativeDisplayable(): ?string
    {
       return $this->getTagParents($this->tag);
    }

    public function getDisplayable(): string
    {
        $firstTrans = $this->tag->getTranslatedTags()->first();
        $name = $this->tag->getTagName();

        if ($firstTrans) {
            $name = $firstTrans->getName();
        }

        return $name;
    }

    public function getOriginal(): Tag
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     * @param bool $slash
     * @return string
     */
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

            $result = $superParent . $name;

            if ($slash) {
                $result .= ' / ';
            }
        }

        return $result;
    }
}
