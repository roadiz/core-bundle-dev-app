<?php

declare(strict_types=1);

namespace Themes\Rozier\Traits;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use RZ\Roadiz\CoreBundle\Node\NodeFactory;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

trait NodesTrait
{
    abstract protected function getNodeFactory(): NodeFactory;

    /**
     * @param string        $title
     * @param TranslationInterface   $translation
     * @param Node|null     $node
     * @param NodeTypeInterface|null $type
     *
     * @return Node
     */
    protected function createNode(
        string $title,
        TranslationInterface $translation,
        Node $node = null,
        NodeTypeInterface $type = null
    ): Node {
        $factory = $this->getNodeFactory();
        $node = $factory->create($title, $type, $translation, $node);

        $entityManager = $this->em();
        $entityManager->flush();

        return $node;
    }

    /**
     * @param array $data
     * @param Node  $node
     *
     * @return NodeType|null
     */
    public function addStackType($data, Node $node): ?NodeType
    {
        if (
            $data['nodeId'] == $node->getId() &&
            !empty($data['nodeTypeId'])
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

    /**
     * @param Node $node
     *
     * @return FormInterface|null
     */
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

    /**
     * @param Node $node
     *
     * @return FormInterface
     */
    protected function buildDeleteForm(Node $node): FormInterface
    {
        $builder = $this->createNamedFormBuilder('delete_node_' . $node->getId())
                        ->add('nodeId', HiddenType::class, [
                            'data' => $node->getId(),
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                        ]);

        return $builder->getForm();
    }

    /**
     * @return FormInterface
     */
    protected function buildEmptyTrashForm(): FormInterface
    {
        $builder = $this->createFormBuilder();
        return $builder->getForm();
    }
}
