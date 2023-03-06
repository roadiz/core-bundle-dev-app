<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\CustomFormOutput;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @deprecated Use Normalizer instead
 */
class CustomFormOutputDataTransformer implements DataTransformerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): object
    {
        if (!$data instanceof CustomForm) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . CustomForm::class);
        }
        $output = new CustomFormOutput();
        $output->name = $data->getDisplayName();
        $output->color = $data->getColor();
        $output->description = $data->getDescription();
        $output->slug = (new AsciiSlugger())->slug($data->getName())->snake()->toString();
        $output->open = $data->isFormStillOpen();
        $output->definitionUrl = $this->urlGenerator->generate('api_custom_forms_item_definition', [
            'id' => $data->getId()
        ]);
        $output->postUrl = $this->urlGenerator->generate('api_custom_forms_item_post', [
            'id' => $data->getId()
        ]);

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return CustomFormOutput::class === $to && $data instanceof CustomForm;
    }
}
