<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\DataTransformer;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TagTransformer implements DataTransformerInterface
{
    private ObjectManager $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param iterable<Tag>|null $tags
     * @return array|string
     */
    public function transform($tags)
    {
        if (empty($tags)) {
            return '';
        }
        $ids = [];
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $ids[] = $tag->getId();
        }
        return $ids;
    }

    /**
     * @param string|array $tagIds
     * @return array
     */
    public function reverseTransform($tagIds)
    {
        if (!$tagIds) {
            return [];
        }

        if (is_array($tagIds)) {
            $ids = $tagIds;
        } else {
            $ids = explode(',', $tagIds);
        }

        $tags = [];
        foreach ($ids as $tagId) {
            $tag = $this->manager
                ->getRepository(Tag::class)
                ->find($tagId)
            ;
            if (null === $tag) {
                throw new TransformationFailedException(sprintf(
                    'A tag with id "%s" does not exist!',
                    $tagId
                ));
            }

            $tags[] = $tag;
        }

        return $tags;
    }
}
