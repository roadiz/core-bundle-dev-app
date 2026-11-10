<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Liform;

use Limenius\Liform\LiformInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Golden-master (snapshot) test pinning the JSON that `liform->transform()` produces
 * for a form covering every transformer Roadiz relies on.
 *
 * Purpose: when migrating from the rezozero/liform* forks back to upstream
 * limenius/liform*, this test proves the JSON output is byte-identical — or shows
 * exactly what drifted. It deliberately exercises the two fork-only additions:
 *   - the `file` transformer (rezozero/liform FileTransformer, with `multiple`);
 *   - translatable `attr.placeholder` (rezozero AbstractTransformer::addAttr()),
 * plus the app-side `date`/`datetime` transformers wired in services.yaml.
 *
 * Baseline flow:
 *   1. Run once on the CURRENT fork — it writes __snapshots__/liform_schema.json
 *      and skips. Commit that file.
 *   2. Swap composer to limenius/liform*, re-run — any output change fails here.
 *
 * Regenerate the baseline on purpose with: UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter LiformSchemaMigrationTest
 */
final class LiformSchemaMigrationTest extends KernelTestCase
{
    private const SNAPSHOT = __DIR__.'/__snapshots__/liform_schema.json';

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testTransformOutputMatchesSnapshot(): void
    {
        /** @var FormFactoryInterface $factory */
        $factory = static::getContainer()->get(FormFactoryInterface::class);
        /** @var LiformInterface $liform */
        $liform = static::getContainer()->get(LiformInterface::class);

        // Register a real translation for the placeholder key, so the fork's
        // AbstractTransformer::addAttr() translation is *observable*: fork emits
        // "Your name please", upstream (no translation) emits the raw "your.name".
        // Two obstacles: (1) in test env the `translator` service is a debug decorator,
        // so we mutate the real component translator behind it (`translator.default`),
        // which the transformers share; (2) the default locale's catalogue is cached to
        // disk, so we inject under an uncached locale to force the loader to run.
        $container = static::getContainer();
        $translator = $container->get(TranslatorInterface::class);
        $realTranslator = $container->has('translator.default')
            ? $container->get('translator.default')
            : $translator;
        if ($realTranslator instanceof \Symfony\Component\Translation\Translator) {
            $realTranslator->addLoader('array', new ArrayLoader());
            $realTranslator->addResource('array', ['your.name' => 'Your name please'], 'zz');
            $realTranslator->setLocale('zz');
        }
        // Verify the injection actually took: if the placeholder key still resolves to
        // itself, we cannot distinguish fork from upstream — skip rather than bake a
        // baseline that would pass on both and give false confidence.
        if ('Your name please' !== $translator->trans('your.name')) {
            self::markTestSkipped('Could not inject a placeholder translation; placeholder feature not asserted.');
        }

        $builder = $factory->createBuilder(FormType::class, null, ['csrf_protection' => false]);
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => ['placeholder' => 'your.name'],
            ])
            ->add('email', EmailType::class, ['required' => true])
            ->add('message', TextareaType::class)
            ->add('age', IntegerType::class)
            ->add('rating', NumberType::class)
            ->add('newsletter', CheckboxType::class, ['required' => false])
            ->add('subject', ChoiceType::class, [
                'choices' => ['Support' => 'support', 'Sales' => 'sales'],
            ])
            ->add('bornAt', DateType::class, ['widget' => 'single_text'])
            ->add('meetingAt', DateTimeType::class, ['widget' => 'single_text'])
            ->add('attachments', FileType::class, ['multiple' => true])
            ->add('photo', FileType::class, ['multiple' => false])
            ->add('tags', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
            ]);

        // Nested sub-form (compound) — mirrors how the controllers embed a child
        // form type, and exercises the compound transformer + a placeholder and file
        // one level deep.
        $builder->add(
            $factory->createNamedBuilder('address', FormType::class, null, ['auto_initialize' => false])
                ->add('street', TextType::class, ['attr' => ['placeholder' => 'your.name']])
                ->add('zipCode', TextType::class)
                ->add('proof', FileType::class)
        );

        $schema = $liform->transform($builder->getForm());
        $json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";

        if (!is_file(self::SNAPSHOT) || false !== getenv('UPDATE_SNAPSHOTS')) {
            @mkdir(\dirname(self::SNAPSHOT), 0o775, true);
            file_put_contents(self::SNAPSHOT, $json);
            self::markTestSkipped('Baseline snapshot written to '.self::SNAPSHOT.' — commit it and re-run to assert.');
        }

        self::assertSame(
            file_get_contents(self::SNAPSHOT),
            $json,
            'liform->transform() output changed: the migration is not JSON-identical. '
            .'If the change is intended, regenerate with UPDATE_SNAPSHOTS=1.'
        );
    }
}
