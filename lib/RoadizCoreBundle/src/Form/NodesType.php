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
        $builder->addModelTransformer(new CallbackTransformer(function ($mixedEntities) {
            if ($mixedEntities instanceof Collection) {
                return $mixedEntities->toArray();
            }
            if (!is_array($mixedEntities)) {
                return [$mixedEntities];
            }

            return $mixedEntities;
        }, function ($mixedIds) use ($options) {
            /** @var NodeRepository $repository */
            $repository = $this->managerRegistry
                ->getRepository(Node::class)
                ->setDisplayingAllNodesStatuses(true);
            if (\is_array($mixedIds) && 0 === count($mixedIds)) {
                return [];
            } elseif (\is_array($mixedIds)) {
                if (false === $options['multiple']) {
                    return $repository->findOneBy(['id' => $mixedIds]);
                }

                return $repository->findBy(['id' => $mixedIds]);
            } elseif (true === $options['multiple']) {
                return [];
            } else {
                return $repository->findOneById($mixedIds);
            }
        }));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'nodes' => [],
        ]);

        $resolver->setAllowedTypes('multiple', ['boolean']);
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
