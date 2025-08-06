<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

trait NodeControllerTrait
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

    public function addStackType(array $data, Node $node, DecoratedNodeTypes $nodeTypesBag): ?NodeType
    {
        if (
            $data['nodeId'] == $node->getId()
            && !empty($data['nodeTypeName'])
        ) {
            $nodeType = $nodeTypesBag->get($data['nodeTypeName']);

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
        if (!$node->isHidingChildren()) {
            return null;
        }

        $defaults = [];
        $builder = $this->createNamedFormBuilder('add_stack_type', $defaults)
            ->add('nodeId', HiddenType::class, [
                'data' => (int) $node->getId(),
            ])
            ->add('nodeTypeName', NodeTypesType::class, [
                'showInvisible' => true,
                'label' => false,
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
            ]);

        return $builder->getForm();
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
        return $this->createFormBuilder()->getForm();
    }
}
