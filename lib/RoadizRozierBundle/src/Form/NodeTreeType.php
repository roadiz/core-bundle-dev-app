<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form;

use RZ\Roadiz\CoreBundle\Bag\DecoratedNodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\RozierBundle\Widget\TreeWidgetFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Node tree embedded type in a node source form.
 *
 * This form type is not published inside Roadiz CMS as it needs
 * NodeTreeWidget which is part of Rozier Theme.
 */
final class NodeTreeType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly RequestStack $requestStack,
        private readonly TreeWidgetFactory $treeWidgetFactory,
        private readonly DecoratedNodeTypes $nodeTypesBag,
    ) {
    }

    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        if (FieldType::CHILDREN_T !== $options['nodeTypeField']->getType()) {
            throw new \RuntimeException('Given field is not a NodeTypeField::CHILDREN_T field.', 1);
        }

        $view->vars['authorizationChecker'] = $this->authorizationChecker;
        /*
         * Inject data as plain document entities
         */
        $view->vars['request'] = $this->requestStack->getCurrentRequest();

        /*
         * Linked types to create quick add buttons
         */
        /** @var NodeTypeField $nodeTypeField */
        $nodeTypeField = $options['nodeTypeField'];
        $defaultValues = $nodeTypeField->getDefaultValuesAsArray();
        foreach ($defaultValues as $key => $value) {
            $defaultValues[$key] = trim((string) $value);
        }

        $nodeTypes = array_values(array_filter(array_map(fn (string $nodeTypeName) => $this->nodeTypesBag->get($nodeTypeName), $defaultValues)));

        $view->vars['linkedTypes'] = $nodeTypes;

        $nodeTree = $this->treeWidgetFactory->createNodeTree(
            $options['nodeSource']->getNode(),
            $options['nodeSource']->getTranslation()
        );
        /*
         * If node-type has been used as default values,
         * we need to restrict node-tree display too.
         */
        if (count($nodeTypes) > 0) {
            $nodeTree->setAdditionalCriteria([
                'nodeTypeName' => array_map(fn (NodeType $nodeType) => $nodeType->getName(), $nodeTypes),
            ]);
        }

        $view->vars['nodeTree'] = $nodeTree;
        $view->vars['nodeStatuses'] = NodeStatus::allLabelsAndValues();
    }

    #[\Override]
    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'childrennodes';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'nodeSource',
            'nodeTypeField',
        ]);

        $resolver->setAllowedTypes('nodeSource', [NodesSources::class]);
        $resolver->setAllowedTypes('nodeTypeField', [NodeTypeField::class]);
    }
}
