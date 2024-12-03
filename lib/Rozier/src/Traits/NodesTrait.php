<?php

declare(strict_types=1);

namespace Themes\Rozier\Traits;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

trait NodesTrait
{
    abstract protected function getNodeFactory(): NodeFactory;

    abstract protected function em(): ObjectManager;

    abstract protected function createNamedFormBuilder(
        string $name = 'form',
        mixed $data = null,
        array $options = [],
    ): FormBuilderInterface;

    protected function createNode(
        string $title,
        TranslationInterface $translation,
        ?Node $node = null,
        ?NodeTypeInterface $type = null,
    ): Node {
        $factory = $this->getNodeFactory();
        $node = $factory->create($title, $type, $translation, $node);

        $entityManager = $this->em();
        $entityManager->flush();

        return $node;
    }

    public function addStackType(array $data, Node $node): ?NodeType
    {
        if (
            $data['nodeId'] == $node->getId()
            && !empty($data['nodeTypeId'])
        ) {
            $nodeType = $this->em()->find(NodeType::class, (int) $data['nodeTypeId']);

            if (null !== $nodeType) {
                $node->addStackType($nodeType);
                $this->em()->flush();

                return $nodeType;
            }
        }

        return null;
    }

    public function buildStackTypesForm(Node $node): ?FormInterface
    {
        if ($node->isHidingChildren()) {
            $defaults = [];
            $builder = $this->createNamedFormBuilder('add_stack_type', $defaults)
                ->add('nodeId', HiddenType::class, [
                    'data' => (int) $node->getId(),
                ])
                ->add('nodeTypeId', NodeTypesType::class, [
                    'showInvisible' => true,
                    'label' => false,
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                ]);

            return $builder->getForm();
        } else {
            return null;
        }
    }

    protected function buildDeleteForm(Node $node): FormInterface
    {
        $builder = $this->createNamedFormBuilder('delete_node_'.$node->getId())
                        ->add('nodeId', HiddenType::class, [
                            'data' => $node->getId(),
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                        ]);

        return $builder->getForm();
    }

    protected function buildEmptyTrashForm(): FormInterface
    {
        $builder = $this->createFormBuilder();

        return $builder->getForm();
    }
}
