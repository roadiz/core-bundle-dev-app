<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TagModel implements ModelInterface
{
    public function __construct(
        private readonly Tag $tag,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function toArray(): array
    {
        $defaultTrans = $this->tag->getTranslatedTagsByDefaultTranslation();
        $name = $this->tag->getTagName();

        if ($defaultTrans) {
            $name = $defaultTrans->getName();
        }

        $result = [
            'id' => $this->tag->getId(),
            'name' => $name,
            'tagName' => $this->tag->getTagName(),
            'color' => $this->tag->getColor(),
            'parent' => $this->getTagParents($this->tag),
            'editUrl' => $this->urlGenerator->generate('tagsEditPage', [
                'tagId' => $this->tag->getId()
            ]),
        ];

        return $result;
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
