<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Form\NodeSource;

use Doctrine\Persistence\Proxy;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Form\DataTransformer\JoinDataTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NodeSourceJoinType extends AbstractConfigurableNodeSourceFieldType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        /*
         * NodeSourceJoinType MUST always be multiple as data is submitted as array
         */
        $resolver->setDefault('multiple', true);
        $resolver->setDefault('_locale', null);
        $resolver->addAllowedTypes('_locale', ['string', 'null']);
    }

    #[\Override]
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
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['_locale'] = $options['_locale'];

        $configuration = $this->getFieldConfiguration($options);
        $displayableData = [];
        /** @var callable $callable */
        $callable = [$options['nodeSource'], $options['nodeTypeField']->getGetterName()];
        $entities = call_user_func($callable);

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
                $displayableCallable = [$entity, $configuration['displayable']];
                if (\is_callable($displayableCallable)) {
                    $data['name'] = call_user_func($displayableCallable);
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
            $displayableCallable = [$entities, $configuration['displayable']];
            if (\is_callable($displayableCallable)) {
                $data['name'] = call_user_func($displayableCallable);
            }
            $displayableData[] = $data;
        }

        $view->vars['data'] = $displayableData;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'join';
    }
}
