<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Repository\AllStatusesNodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;

final readonly class UniversalDataDuplicator
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private AllStatusesNodesSourcesRepository $allStatusesNodesSourcesRepository,
        private NodeTypes $nodeTypesBag,
    ) {
    }

    /**
     * Duplicate node-source universal to any other language source for the same node.
     *
     * **Be careful, this method does not flush.**
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function duplicateUniversalContents(NodesSources $source): bool
    {
        /*
         * Only if source is default translation.
         * Non-default translation source should not contain universal fields.
         */
        if ($source->getTranslation()->isDefaultTranslation() || !$this->hasDefaultTranslation($source)) {
            $fields = $this->nodeTypesBag->get($source->getNodeTypeName())->getFields();
            /** @var NodeTypeField[] $universalFields */
            $universalFields = $fields->filter(function (NodeTypeField $field) {
                return $field->isUniversal();
            });

            if (count($universalFields) > 0) {
                $otherSources = $this->allStatusesNodesSourcesRepository->findBy([
                    'node' => $source->getNode(),
                    'id' => ['!=', $source->getId()],
                ]);

                foreach ($universalFields as $universalField) {
                    /** @var NodesSources $otherSource */
                    foreach ($otherSources as $otherSource) {
                        if (!$universalField->isVirtual()) {
                            $this->duplicateNonVirtualField($source, $otherSource, $universalField);
                        } else {
                            switch ($universalField->getType()) {
                                case FieldType::DOCUMENTS_T:
                                    $this->duplicateDocumentsField($source, $otherSource, $universalField);
                                    break;
                                case FieldType::MULTI_PROVIDER_T:
                                case FieldType::SINGLE_PROVIDER_T:
                                case FieldType::MANY_TO_ONE_T:
                                case FieldType::MANY_TO_MANY_T:
                                    $this->duplicateNonVirtualField($source, $otherSource, $universalField);
                                    break;
                            }
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function hasDefaultTranslation(NodesSources $source): bool
    {
        /** @var TranslationRepository $translationRepository */
        $translationRepository = $this->managerRegistry->getRepository(Translation::class);
        /** @var Translation $defaultTranslation */
        $defaultTranslation = $translationRepository->findDefault();

        $sourceCount = $this->allStatusesNodesSourcesRepository->countBy([
            'node' => $source->getNode(),
            'translation' => $defaultTranslation,
        ]);

        return 1 === $sourceCount;
    }

    private function duplicateNonVirtualField(
        NodesSources $universalSource,
        NodesSources $destSource,
        NodeTypeFieldInterface $field,
    ): void {
        $getter = $field->getGetterName();
        $setter = $field->getSetterName();

        $destSource->$setter($universalSource->$getter());
    }

    private function duplicateDocumentsField(
        NodesSources $universalSource,
        NodesSources $destSource,
        NodeTypeFieldInterface $field,
    ): void {
        $newDocuments = $this->managerRegistry
            ->getRepository(NodesSourcesDocuments::class)
            ->findBy(['nodeSource' => $universalSource, 'fieldName' => $field->getName()]);

        $formerDocuments = $this->managerRegistry
            ->getRepository(NodesSourcesDocuments::class)
            ->findBy(['nodeSource' => $destSource, 'fieldName' => $field->getName()]);

        $manager = $this->managerRegistry->getManagerForClass(NodesSourcesDocuments::class);
        if (null === $manager) {
            return;
        }

        /* Delete former documents */
        if (count($formerDocuments) > 0) {
            foreach ($formerDocuments as $formerDocument) {
                $manager->remove($formerDocument);
            }
        }
        /* Add new documents */
        if (count($newDocuments) > 0) {
            $position = 1;
            /** @var NodesSourcesDocuments $newDocument */
            foreach ($newDocuments as $newDocument) {
                $nsDoc = new NodesSourcesDocuments($destSource, $newDocument->getDocument(), $field);
                $nsDoc->setPosition($position);
                ++$position;

                $manager->persist($nsDoc);
            }
        }
    }
}
