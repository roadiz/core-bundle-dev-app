<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\ORM\NonUniqueResultException;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\Documents\Models\FolderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class DocumentDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private DocumentRepository $repository,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DocumentDto::class => false,
        ];
    }

    /**
     * @return array|\ArrayObject|bool|float|int|string|null
     *
     * @throws ExceptionInterface
     * @throws NonUniqueResultException
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        /* @var DocumentDto $object */
        if (is_array($data)) {
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];
            $document = $this->repository->findOneBy(['id' => $object->getId()]);
            if (\in_array('document_folders_all', $serializationGroups, true)) {
                $data['folders'] = $document->getFolders()
                    ->map(function (FolderInterface $folder) use ($format, $context) {
                        return $this->normalizer->normalize($folder, $format, $context);
                    })->getValues();
            } elseif (\in_array('document_folders', $serializationGroups, true)) {
                $data['folders'] = $document->getFolders()->filter(function (FolderInterface $folder) {
                    return $folder->getVisible();
                })->map(function (FolderInterface $folder) use ($format, $context) {
                    return $this->normalizer->normalize($folder, $format, $context);
                })->getValues();
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }
}
