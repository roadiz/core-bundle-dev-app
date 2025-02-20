<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodesType extends AbstractType
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function (mixed $mixedEntities) use ($options): array {
                if ($mixedEntities instanceof Collection) {
                    $mixedEntities = $mixedEntities->toArray();
                }
                if (!\is_array($mixedEntities)) {
                    $mixedEntities = [$mixedEntities];
                }

                $mixedIds = array_map(fn (mixed $node) => $node instanceof Node ? $node->getId() : $node, $mixedEntities);

                return $mixedIds;
            },
            function (array|int|string|null $mixedIds) use ($options) {
                if (null === $mixedIds || (\is_array($mixedIds) && 0 === count($mixedIds))) {
                    return $options['asMultiple'] ? [] : null;
                }

                if (!\is_array($mixedIds)) {
                    $mixedIds = [$mixedIds];
                } else {
                    $mixedIds = array_values($mixedIds);
                }

                /** @var NodeRepository $repository */
                $repository = $this->managerRegistry
                    ->getRepository(Node::class)
                    ->setDisplayingAllNodesStatuses(true);

                return $options['asMultiple'] ? $repository->findBy(['id' => $mixedIds]) : $repository->find($mixedIds[0]);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            // Need to use a different option name to avoid early transformation exceptions
            'asMultiple' => true,
            'nodes' => [],
        ]);

        $resolver->setAllowedTypes('multiple', ['boolean']);
        $resolver->setAllowedTypes('asMultiple', ['boolean']);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        /*
         * Inject data as plain nodes entities
         */
        if (!empty($options['nodes'])) {
            $view->vars['data'] = $options['nodes'];
        }
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'nodes';
    }
}
