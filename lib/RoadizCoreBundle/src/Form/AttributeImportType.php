<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class AttributeImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'label' => 'attributes.import_form.file.label',
            'help' => 'attributes.import_form.file.help',
            'constraints' => [
                new File([
                    'mimeTypes' => ['application/json', 'text/json', 'text/plain'],
                ]),
            ],
        ]);
    }
}
