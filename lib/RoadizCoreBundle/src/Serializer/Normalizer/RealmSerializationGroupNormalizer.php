<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\RealmVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class RealmSerializationGroupNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'REALM_SERIALIZER_NORMALIZER_ALREADY_CALLED';
    private Security $security;
    private ManagerRegistry $managerRegistry;

    /**
     * @param Security $security
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(Security $security, ManagerRegistry $managerRegistry)
    {
        $this->security = $security;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof NodesSources;
    }

    /**
     * @inheritDoc
     * @return array|string|int|float|bool|\ArrayObject|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $realms = $this->getAuthorizedRealmsForObject($object);

        foreach ($realms as $realm) {
            if (!empty($realm->getSerializationGroup())) {
                $context['groups'][] = $realm->getSerializationGroup();
            }
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * @return Realm[]
     */
    private function getAuthorizedRealmsForObject(NodesSources $object): array
    {
        $realms = $this->managerRegistry->getRepository(Realm::class)->findByNode($object->getNode());

        return array_filter($realms, function (Realm $realm) {
            return $this->security->isGranted(RealmVoter::READ, $realm);
        });
    }
}
