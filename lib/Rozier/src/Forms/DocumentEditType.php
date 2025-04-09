<?php

declare(strict_types=1);

namespace Themes\Rozier\Forms;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Form\ColorType;
use RZ\Roadiz\CoreBundle\Form\Constraint\UniqueFilename;
use RZ\Roadiz\CoreBundle\Form\DocumentCollectionType;
use RZ\Roadiz\CoreBundle\Form\JsonType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class DocumentEditType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Document $document */
        $document = $builder->getData();
        $builder->add('referer', HiddenType::class, [
            'data' => $options['referer'],
            'mapped' => false,
        ]);

        if ($document->isLocal()) {
            $builder->add('filename', TextType::class, [
                'label' => 'filename',
                'empty_data' => '',
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/\.[a-z0-9]+$/i',
                        'htmlPattern' => '.[a-z0-9]+$',
                        'message' => 'value_is_not_a_valid_filename',
                    ]),
                    new UniqueFilename([
                        'document' => $document,
                    ]),
                ],
            ])
                ->add('mimeType', TextType::class, [
                    'label' => 'document.mimeType',
                    'empty_data' => '',
                    'required' => true,
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                ])
                ->add('private', CheckboxType::class, [
                    'label' => 'private',
                    'help' => 'document.private.help',
                    'required' => false,
                ])
            ;
        }

        $builder->add('newDocument', FileType::class, [
            'label' => 'overwrite.document',
            'required' => false,
            'mapped' => false,
            'constraints' => [
                new File(),
            ],
        ])
            ->add('embed', DocumentEmbedType::class, [
                'label' => 'document.embed',
                'required' => false,
                'inherit_data' => true,
                'document_platforms' => $options['document_platforms'],
            ])
            ->add('imageAverageColor', ColorType::class, [
                'label' => 'document.imageAverageColor',
                'help' => 'document.imageAverageColor.help',
                'required' => false,
            ])
        ;

        if ($document->isImage() || $document->isVideo() || $document->isEmbed()) {
            $builder->add('imageWidth', IntegerType::class, [
                'label' => 'document.width',
                'required' => false,
            ]);
            $builder->add('imageHeight', IntegerType::class, [
                'label' => 'document.height',
                'required' => false,
            ]);
        }

        if ($document->isAudio() || $document->isVideo() || $document->isEmbed()) {
            $builder->add('mediaDuration', IntegerType::class, [
                'label' => 'document.duration',
                'required' => false,
            ]);
        }

        if ($document->isProcessable()) {
            $builder->add('imageCropAlignment', ImageCropAlignmentType::class, [
                'label' => 'document.imageCropAlignment',
                'help' => 'document.imageCropAlignment.help',
                'required' => false,
            ]);
            $builder->add('hotspot', JsonType::class, [
                'label' => 'document.hotspot',
                'help' => 'document.hotspot.help',
                'required' => false,
            ]);
            $builder->get('hotspot')
            ->addModelTransformer(new CallbackTransformer(
                function (mixed $hotspot): string {
                    return json_encode($hotspot, JSON_THROW_ON_ERROR);
                },
                function (mixed $hotspot): ?array {
                    if (!\is_string($hotspot)) {
                        return null;
                    }
                    try {
                        return json_decode($hotspot, true, flags: JSON_THROW_ON_ERROR);
                    } catch (\JsonException $e) {
                        throw new TransformationFailedException($e->getMessage(), previous: $e);
                    }
                }
            ));
        }

        /*
         * Display thumbnails only if current Document is original.
         */
        if (null === $document->getOriginal()) {
            $builder->add('thumbnails', DocumentCollectionType::class, [
                'label' => 'document.thumbnails',
                'multiple' => true,
                'required' => false,
            ]);
        }

        $builder->add('folders', FolderCollectionType::class, [
            'label' => 'folders',
            'multiple' => true,
            'required' => false,
        ]);

        if ($this->security->isGranted('ROLE_ACCESS_DOCUMENTS_CREATION_DATE')) {
            $builder->add('createdAt', DateTimeType::class, [
                'label' => 'createdAt',
                'help' => 'document.createdAt.help',
                'required' => false,
                'attr' => [
                    'class' => 'rz-datetime-field',
                ],
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'hour' => 'hour',
                    'minute' => 'minute',
                ],
                'constraints' => [
                    new LessThanOrEqual($builder->getData()->getUpdatedAt()),
                    new LessThanOrEqual('now'),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);

        $resolver->setRequired('referer');
        $resolver->setAllowedTypes('referer', ['null', 'string']);

        $resolver->setRequired('document_platforms');
        $resolver->setAllowedTypes('document_platforms', ['array']);
    }

    public function getBlockPrefix(): string
    {
        return 'document_edit';
    }
}
