<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ExplorerProviderItemTransformer implements DataTransformerInterface
{
    protected ExplorerProviderInterface $explorerProvider;
    protected bool $multiple;
    protected bool $useCollection;

    public function __construct(
        ExplorerProviderInterface $explorerProvider,
        bool $multiple = true,
        bool $useCollection = false,
    ) {
        $this->explorerProvider = $explorerProvider;
        $this->multiple = $multiple;
        $this->useCollection = $useCollection;
    }

    public function transform(mixed $value): array|string
    {
        if (!empty($value) && $this->explorerProvider->supports($value)) {
            $item = $this->explorerProvider->toExplorerItem($value);
            if (!$item instanceof ExplorerItemInterface) {
                throw new TransformationFailedException('Cannot transform model to ExplorerItem.');
            }

            return [$item];
        } elseif (!empty($value) && is_iterable($value)) {
            $idArray = [];
            foreach ($value as $entity) {
                if ($this->explorerProvider->supports($entity)) {
                    $item = $this->explorerProvider->toExplorerItem($entity);
                    if (!$item instanceof ExplorerItemInterface) {
                        throw new TransformationFailedException('Cannot transform model to ExplorerItem.');
                    }
                    $idArray[] = $item;
                } else {
                    throw new TransformationFailedException('Cannot transform model to ExplorerItem.');
                }
            }

            return array_filter($idArray);
        }

        return '';
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (empty($value)) {
            $items = [];
        } elseif ($value instanceof ExplorerItemInterface) {
            $items = [$value];
        } elseif (\is_string($value) || \is_int($value)) {
            $items = $this->explorerProvider->getItemsById([$value]);
        } elseif (\is_array($value) && is_scalar(reset($value))) {
            $items = $this->explorerProvider->getItemsById($value);
        } elseif (\is_array($value) && reset($value) instanceof ExplorerItemInterface) {
            $items = $value;
        } else {
            throw new TransformationFailedException('Cannot reverse transform submitted data to model.');
        }

        $originals = [];
        foreach ($items as $item) {
            $originals[] = $item->getOriginal();
        }

        if ($this->multiple) {
            if ($this->useCollection) {
                return new ArrayCollection(array_filter($originals));
            }

            return array_filter($originals);
        }

        return array_filter($originals)[0] ?? null;
    }
}
