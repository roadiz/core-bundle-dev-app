<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Repository\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class TagArrayTransformer implements DataTransformerInterface
{
    public function __construct(private TagRepository $tagRepository)
    {
    }

    /**
     * @param iterable<Tag>|null $value
     */
    #[\Override]
    public function transform(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        if ($value instanceof Tag) {
            $value = [$value];
        }

        if (!is_iterable($value)) {
            throw new TransformationFailedException('Expected an iterable of Tag entities.');
        }

        $ids = [];
        /** @var Tag $tag */
        foreach ($value as $tag) {
            $ids[] = $tag->getId();
        }

        return $ids;
    }

    #[\Override]
    public function reverseTransform(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            $ids = $value;
        } elseif (is_string($value)) {
            $ids = explode(',', $value);
        } elseif (is_numeric($value)) {
            $ids = [(int) $value];
        } else {
            throw new TransformationFailedException('Expected an array of tag IDs.');
        }

        $tags = [];
        foreach ($ids as $tagId) {
            $tag = $this->tagRepository->find($tagId);
            if (null === $tag) {
                throw new TransformationFailedException(sprintf('A tag with id "%s" does not exist!', $tagId));
            }

            $tags[] = $tag;
        }

        return $tags;
    }
}
