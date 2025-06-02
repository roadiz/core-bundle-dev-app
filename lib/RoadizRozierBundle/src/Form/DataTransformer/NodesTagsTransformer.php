<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesTags;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\Form\DataTransformerInterface;

class NodesTagsTransformer implements DataTransformerInterface
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @param iterable<NodesTags> $value
     *
     * @return int[]
     */
    #[\Override]
    public function transform(mixed $value): array
    {
        $ids = [];
        if (\is_iterable($value)) {
            foreach ($value as $nodesTag) {
                $ids[] = (int) $nodesTag->getTag()->getId();
            }
        }

        return $ids;
    }

    /**
     * @param iterable<int|string> $value
     *
     * @return Collection<int, NodesTags>
     */
    #[\Override]
    public function reverseTransform(mixed $value): Collection
    {
        $nodesTags = [];
        if (\is_iterable($value)) {
            $i = 0;
            /** @var int|string $tagId */
            foreach ($value as $tagId) {
                $tag = $this->managerRegistry
                    ->getRepository(Tag::class)
                    ->find($tagId);
                if ($tag instanceof Tag) {
                    $nodesTags[] = (new NodesTags())
                        ->setTag($tag)
                        ->setPosition(++$i)
                    ;
                }
            }
        }

        return new ArrayCollection($nodesTags);
    }
}
