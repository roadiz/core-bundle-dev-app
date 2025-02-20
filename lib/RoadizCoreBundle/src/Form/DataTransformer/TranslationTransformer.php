<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class TranslationTransformer implements DataTransformerInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @param Translation|null $value
     */
    public function transform(mixed $value): int|string|null
    {
        if (!($value instanceof PersistableInterface)) {
            return null;
        }

        return $value->getId();
    }

    public function reverseTransform(mixed $value): ?Translation
    {
        if (!$value) {
            return null;
        }

        /** @var Translation|null $translation */
        $translation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->find($value)
        ;

        if (null === $translation) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf('A translation with id "%s" does not exist!', $value));
        }

        return $translation;
    }
}
