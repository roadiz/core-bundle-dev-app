<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms\NodeSource;

use Doctrine\Persistence\Proxy;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\JoinDataTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeSourceJoinType extends AbstractConfigurableNodeSourceFieldType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        /*
         * NodeSourceJoinType MUST always be multiple as data is submitted as array
         */
        $resolver->setDefault('multiple', true);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $configuration = $this->getFieldConfiguration($options);

        $builder->addModelTransformer(new JoinDataTransformer(
            $options['nodeTypeField'],
            $this->managerRegistry,
            $configuration['classname']
        ));
    }

    /**
     * Pass data to form twig template.
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $configuration = $this->getFieldConfiguration($options);
        $displayableData = [];

        $entities = call_user_func([$options['nodeSource'], $options['nodeTypeField']->getGetterName()]);

        if ($entities instanceof \Traversable) {
            /** @var PersistableInterface $entity */
            foreach ($entities as $entity) {
                if ($entity instanceof Proxy) {
                    $entity->__load();
                }
                $data = [
                    'id' => $entity->getId(),
                    'classname' => $configuration['classname'],
                ];
                if (is_callable([$entity, $configuration['displayable']])) {
                    $data['name'] = call_user_func([$entity, $configuration['displayable']]);
                }
                $displayableData[] = $data;
            }
        } elseif ($entities instanceof PersistableInterface) {
            if ($entities instanceof Proxy) {
                $entities->__load();
            }
            $data = [
                'id' => $entities->getId(),
                'classname' => $configuration['classname'],
            ];
            if (is_callable([$entities, $configuration['displayable']])) {
                $data['name'] = call_user_func([$entities, $configuration['displayable']]);
            }
            $displayableData[] = $data;
        }

        $view->vars['data'] = $displayableData;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'join';
    }
}
