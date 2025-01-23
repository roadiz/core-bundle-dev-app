<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\AbstractField;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Form\CompareDatetimeType;
use RZ\Roadiz\CoreBundle\Form\CompareDateType;
use RZ\Roadiz\CoreBundle\Form\ExtendedBooleanType;
use RZ\Roadiz\CoreBundle\Form\NodeStatesType;
use RZ\Roadiz\CoreBundle\Form\NodeTypesType;
use RZ\Roadiz\CoreBundle\Form\SeparatorType;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Themes\Rozier\Forms\NodeSource\NodeSourceType;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class SearchController extends RozierApp
{
    protected bool $pagination = true;
    protected ?int $itemPerPage = null;

    public function __construct(
        protected readonly NodeTypes $nodeTypesBag,
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly SerializerInterface $serializer,
    ) {
    }

    public function isBlank(mixed $var): bool
    {
        return empty($var) && !is_numeric($var);
    }

    public function notBlank(mixed $var): bool
    {
        return !$this->isBlank($var);
    }

    protected function appendDateTimeCriteria(array &$data, string $fieldName): array
    {
        $date = $data[$fieldName]['compareDatetime'];
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d H:i:s');
        }
        $data[$fieldName] = [
            $data[$fieldName]['compareOp'],
            $date,
        ];

        return $data;
    }

    protected function processCriteria(array $data, string $prefix = ''): array
    {
        if (!empty($data[$prefix.'nodeName'])) {
            if (!isset($data[$prefix.'nodeName_exact']) || true !== $data[$prefix.'nodeName_exact']) {
                $data[$prefix.'nodeName'] = ['LIKE', '%'.$data[$prefix.'nodeName'].'%'];
            }
        }

        if (key_exists($prefix.'nodeName_exact', $data)) {
            unset($data[$prefix.'nodeName_exact']);
        }

        if (isset($data[$prefix.'parent']) && !$this->isBlank($data[$prefix.'parent'])) {
            if ('null' == $data[$prefix.'parent'] || 0 == $data[$prefix.'parent']) {
                $data[$prefix.'parent'] = null;
            }
        }

        if (isset($data[$prefix.'visible'])) {
            $data[$prefix.'visible'] = (bool) $data[$prefix.'visible'];
        }

        if (isset($data[$prefix.'createdAt'])) {
            $this->appendDateTimeCriteria($data, $prefix.'createdAt');
        }

        if (isset($data[$prefix.'updatedAt'])) {
            $this->appendDateTimeCriteria($data, $prefix.'updatedAt');
        }

        if (isset($data[$prefix.'limitResult'])) {
            $this->pagination = false;
            $this->itemPerPage = (int) $data[$prefix.'limitResult'];
            unset($data[$prefix.'limitResult']);
        }

        /*
         * no need to prefix tags
         */
        if (isset($data['tags'])) {
            $data['tags'] = array_map('trim', explode(',', $data['tags']));
            foreach ($data['tags'] as $key => $value) {
                $data['tags'][$key] = $this->managerRegistry->getRepository(Tag::class)->findByPath($value);
            }
            array_filter($data['tags']);
        }

        return $data;
    }

    protected function processCriteriaNodetype(array $data, NodeType $nodetype): array
    {
        $fields = $nodetype->getFields();
        foreach ($data as $key => $value) {
            if ('title' === $key) {
                $data['title'] = ['LIKE', '%'.$value.'%'];
                if (isset($data[$key.'_exact'])) {
                    if (true === $data[$key.'_exact']) {
                        $data['title'] = $value;
                    }
                }
            } elseif ('publishedAt' === $key) {
                $this->appendDateTimeCriteria($data, 'publishedAt');
            } else {
                /** @var NodeTypeField $field */
                foreach ($fields as $field) {
                    if ($key == $field->getName()) {
                        if (
                            AbstractField::MARKDOWN_T === $field->getType()
                            || AbstractField::STRING_T === $field->getType()
                            || AbstractField::YAML_T === $field->getType()
                            || AbstractField::JSON_T === $field->getType()
                            || AbstractField::TEXT_T === $field->getType()
                            || AbstractField::EMAIL_T === $field->getType()
                            || AbstractField::CSS_T === $field->getType()
                        ) {
                            $data[$field->getVarName()] = ['LIKE', '%'.$value.'%'];
                            if (isset($data[$key.'_exact']) && true === $data[$key.'_exact']) {
                                $data[$field->getVarName()] = $value;
                            }
                        } elseif (AbstractField::BOOLEAN_T === $field->getType()) {
                            $data[$field->getVarName()] = (bool) $value;
                        } elseif (AbstractField::MULTIPLE_T === $field->getType()) {
                            $data[$field->getVarName()] = implode(',', $value);
                        } elseif (AbstractField::DATETIME_T === $field->getType()) {
                            $this->appendDateTimeCriteria($data, $key);
                        } elseif (AbstractField::DATE_T === $field->getType()) {
                            $this->appendDateTimeCriteria($data, $key);
                        }
                    }
                }
            }
            if (key_exists($key.'_exact', $data)) {
                unset($data[$key.'_exact']);
            }
        }

        return $data;
    }

    /**
     * @throws RuntimeError
     */
    public function searchNodeAction(Request $request): Response
    {
        $builder = $this->buildSimpleForm('');
        $form = $this->addButtons($builder)->getForm();
        $form->handleRequest($request);

        $builderNodeType = $this->buildNodeTypeForm();

        /** @var Form $nodeTypeForm */
        $nodeTypeForm = $builderNodeType->getForm();
        $nodeTypeForm->handleRequest($request);

        if (null !== $response = $this->handleNodeTypeForm($nodeTypeForm)) {
            $response->prepare($request);

            return $response->send();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = [];
            foreach ($form->getData() as $key => $value) {
                if (
                    (!is_array($value) && $this->notBlank($value))
                    || (is_array($value) && isset($value['compareDatetime']))
                ) {
                    $data[$key] = $value;
                }
            }
            $data = $this->processCriteria($data);
            $listManager = $this->createEntityListManager(
                Node::class,
                $data
            );
            $listManager->setDisplayingNotPublishedNodes(true);
            $listManager->setDisplayingAllNodesStatuses(true);

            if (false === $this->pagination) {
                $listManager->setItemPerPage($this->itemPerPage ?? 999);
                $listManager->disablePagination();
            }
            $listManager->handle();

            $this->assignation['filters'] = $listManager->getAssignation();
            $this->assignation['nodes'] = $listManager->getEntities();
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['nodeTypeForm'] = $nodeTypeForm->createView();
        $this->assignation['filters']['searchDisable'] = true;

        return $this->render('@RoadizRozier/search/list.html.twig', $this->assignation);
    }

    /**
     * @throws RuntimeError
     */
    public function searchNodeSourceAction(Request $request, int $nodetypeId): Response
    {
        /** @var NodeType|null $nodetype */
        $nodetype = $this->nodeTypesBag->getById($nodetypeId);

        $builder = $this->buildSimpleForm('__node__');
        $this->extendForm($builder, $nodetype);
        $this->addButtons($builder, true);

        $form = $builder->getForm();
        $form->handleRequest($request);

        $builderNodeType = $this->buildNodeTypeForm($nodetypeId);
        $nodeTypeForm = $builderNodeType->getForm();
        $nodeTypeForm->handleRequest($request);

        if (null !== $response = $this->handleNodeTypeForm($nodeTypeForm)) {
            return $response;
        }

        if (null !== $response = $this->handleNodeForm($form, $nodetype)) {
            return $response;
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['nodeType'] = $nodetype;
        $this->assignation['filters']['searchDisable'] = true;

        return $this->render('@RoadizRozier/search/list.html.twig', $this->assignation);
    }

    /**
     * Build node-type selection form.
     */
    protected function buildNodeTypeForm(?int $nodetypeId = null): FormBuilderInterface
    {
        $builderNodeType = $this->formFactory->createNamedBuilder('nodeTypeForm', FormType::class, [], ['method' => 'get']);
        $builderNodeType->add(
            'nodetype',
            NodeTypesType::class,
            [
                'label' => 'nodeType',
                'placeholder' => 'ignore',
                'required' => false,
                'data' => $nodetypeId,
                'showInvisible' => true,
            ]
        );

        return $builderNodeType;
    }

    protected function addButtons(FormBuilderInterface $builder, bool $export = false): FormBuilderInterface
    {
        $builder->add('search', SubmitType::class, [
            'label' => 'search.a.node',
            'attr' => [
                'class' => 'uk-button uk-button-primary',
            ],
        ]);

        if ($export) {
            $builder->add('export', SubmitType::class, [
                'label' => 'export.all.nodesSource',
                'attr' => [
                    'class' => 'uk-button rz-no-ajax',
                ],
            ]);
        }

        return $builder;
    }

    protected function handleNodeTypeForm(FormInterface $nodeTypeForm): ?RedirectResponse
    {
        if ($nodeTypeForm->isSubmitted() && $nodeTypeForm->isValid()) {
            if (empty($nodeTypeForm->getData()['nodetype'])) {
                return $this->redirectToRoute('searchNodePage');
            } else {
                return $this->redirectToRoute(
                    'searchNodeSourcePage',
                    [
                        'nodetypeId' => $nodeTypeForm->getData()['nodetype'],
                    ]
                );
            }
        }

        return null;
    }

    protected function handleNodeForm(FormInterface $form, NodeType $nodetype): ?Response
    {
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }
        $data = [];
        foreach ($form->getData() as $key => $value) {
            if (
                (!is_array($value) && $this->notBlank($value))
                || (is_array($value) && isset($value['compareDatetime']))
                || (is_array($value) && isset($value['compareDate']))
                || (is_array($value) && [] != $value && !isset($value['compareOp']))
            ) {
                if (\is_string($key) & \str_contains($key, '__node__')) {
                    /** @var string $newKey */
                    $newKey = \str_replace('__node__', 'node.', $key);
                    $data[$newKey] = $value;
                } else {
                    $data[$key] = $value;
                }
            }
        }
        $data = $this->processCriteria($data, 'node.');
        $data = $this->processCriteriaNodetype($data, $nodetype);

        $listManager = $this->createEntityListManager(
            $nodetype->getSourceEntityFullQualifiedClassName(),
            $data
        );
        $listManager->setDisplayingNotPublishedNodes(true);
        $listManager->setDisplayingAllNodesStatuses(true);
        if (false === $this->pagination) {
            $listManager->setItemPerPage($this->itemPerPage ?? 999);
            $listManager->disablePagination();
        }
        $listManager->handle();
        $entities = $listManager->getEntities();
        $nodes = [];
        foreach ($entities as $nodesSource) {
            if (!in_array($nodesSource->getNode(), $nodes)) {
                $nodes[] = $nodesSource->getNode();
            }
        }
        /*
         * Export all entries into XLSX format
         */
        $button = $form->get('export');
        if ($button instanceof ClickableInterface && $button->isClicked()) {
            $filename = 'search-'.$nodetype->getName().'-'.date('YmdHis').'.csv';
            $response = new StreamedResponse(function () use ($entities) {
                echo $this->serializer->serialize($entities, 'csv', [
                    'groups' => [
                        'nodes_sources',
                        'urls',
                        'tag_base',
                        'document_display',
                    ],
                ]);
            });
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set(
                'Content-Disposition',
                $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $filename
                )
            );

            return $response;
        }

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['nodesSources'] = $entities;
        $this->assignation['nodes'] = $nodes;

        return null;
    }

    protected function buildSimpleForm(string $prefix = ''): FormBuilderInterface
    {
        /** @var FormBuilder $builder */
        $builder = $this->createFormBuilder([], ['method' => 'get']);

        $builder->add($prefix.'status', NodeStatesType::class, [
            'label' => 'node.status',
            'required' => false,
        ]);
        $builder->add(
            $builder->create('status_group', FormType::class, [
                'label' => false,
                'inherit_data' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-col-status-group',
                ],
            ])
            ->add($prefix.'visible', ExtendedBooleanType::class, [
                'label' => 'visible',
            ])
            ->add($prefix.'locked', ExtendedBooleanType::class, [
                'label' => 'locked',
            ])
            ->add($prefix.'sterile', ExtendedBooleanType::class, [
                'label' => 'sterile-status',
            ])
            ->add($prefix.'hideChildren', ExtendedBooleanType::class, [
                'label' => 'hiding-children',
            ])
        );
        $builder->add(
            $this->createTextSearchForm($builder, $prefix.'nodeName', 'nodeName')
        );
        $builder->add($prefix.'parent', TextType::class, [
            'label' => 'node.id.parent',
            'required' => false,
        ])
            ->add($prefix.'createdAt', CompareDatetimeType::class, [
                'label' => 'created.at',
                'inherit_data' => false,
                'required' => false,
            ])
            ->add($prefix.'updatedAt', CompareDatetimeType::class, [
                'label' => 'updated.at',
                'inherit_data' => false,
                'required' => false,
            ])
            ->add($prefix.'limitResult', NumberType::class, [
                'label' => 'node.limit.result',
                'required' => false,
                'constraints' => [
                    new GreaterThan(0),
                ],
            ])
            // No need to prefix tags
            ->add('tags', TextType::class, [
                'label' => 'node.tags',
                'required' => false,
                'attr' => ['class' => 'rz-tag-autocomplete'],
            ])
            // No need to prefix tags
            ->add('tagExclusive', CheckboxType::class, [
                'label' => 'node.tag.exclusive',
                'required' => false,
            ])
        ;

        return $builder;
    }

    protected function createTextSearchForm(
        FormBuilderInterface $builder,
        string $formName,
        string $label,
    ): FormBuilderInterface {
        return $builder->create($formName.'_group', FormType::class, [
            'label' => false,
            'inherit_data' => true,
            'mapped' => false,
            'attr' => [
                'class' => 'form-col-search-group',
            ],
        ])
            ->add($formName, TextType::class, [
                'label' => $label,
                'required' => false,
            ])
            ->add($formName.'_exact', CheckboxType::class, [
                'label' => 'exact_search',
                'required' => false,
            ])
        ;
    }

    private function extendForm(FormBuilderInterface $builder, NodeType $nodetype): void
    {
        $fields = $nodetype->getFields();

        $builder->add(
            'nodetypefield',
            SeparatorType::class,
            [
                'label' => 'nodetypefield',
                'attr' => ['class' => 'label-separator'],
            ]
        );
        $builder->add(
            $this->createTextSearchForm($builder, 'title', 'title')
        );
        if ($nodetype->isPublishable()) {
            $builder->add(
                'publishedAt',
                CompareDatetimeType::class,
                [
                    'label' => 'publishedAt',
                    'required' => false,
                ]
            );
        }

        foreach ($fields as $field) {
            $option = ['label' => $field->getLabel()];
            $option['required'] = false;
            if ($field->isVirtual()) {
                continue;
            }
            /*
             * Prevent searching on complex fields
             */
            if (
                $field->isMultipleProvider()
                || $field->isSingleProvider()
                || $field->isCollection()
                || $field->isManyToMany()
                || $field->isManyToOne()
            ) {
                continue;
            }

            if (AbstractField::ENUM_T === $field->getType()) {
                $choices = explode(',', $field->getDefaultValues() ?? '');
                $choices = array_map('trim', $choices);
                $choices = array_combine(array_values($choices), array_values($choices));
                $type = ChoiceType::class;
                $option['placeholder'] = 'ignore';
                $option['required'] = false;
                $option['expanded'] = false;
                if (count($choices) < 4) {
                    $option['expanded'] = true;
                }
                $option['choices'] = $choices;
            } elseif (AbstractField::MULTIPLE_T === $field->getType()) {
                $choices = explode(',', $field->getDefaultValues() ?? '');
                $choices = array_map('trim', $choices);
                $choices = array_combine(array_values($choices), array_values($choices));
                $type = ChoiceType::class;
                $option['choices'] = $choices;
                $option['placeholder'] = 'ignore';
                $option['required'] = false;
                $option['multiple'] = true;
                $option['expanded'] = false;
                if (count($choices) < 4) {
                    $option['expanded'] = true;
                }
            } elseif (AbstractField::DATETIME_T === $field->getType()) {
                $type = CompareDatetimeType::class;
            } elseif (AbstractField::DATE_T === $field->getType()) {
                $type = CompareDateType::class;
            } else {
                $type = NodeSourceType::getFormTypeFromFieldType($field);
            }

            if (
                AbstractField::MARKDOWN_T === $field->getType()
                || AbstractField::STRING_T === $field->getType()
                || AbstractField::TEXT_T === $field->getType()
                || AbstractField::EMAIL_T === $field->getType()
                || AbstractField::JSON_T === $field->getType()
                || AbstractField::YAML_T === $field->getType()
                || AbstractField::CSS_T === $field->getType()
            ) {
                $builder->add(
                    $this->createTextSearchForm($builder, $field->getVarName(), $field->getLabel())
                );
            } else {
                $builder->add($field->getVarName(), $type, $option);
            }
        }
    }
}
