<?php

declare(strict_types=1);

namespace App\DataTransformer;

use App\Dto\NSPageOutput;
use App\GeneratedEntity\NSPage;
use RZ\Roadiz\CoreBundle\Api\DataTransformer\NodesSourcesOutputDataTransformer;

class NSPageOutputDataTransformer extends NodesSourcesOutputDataTransformer
{
    /**
     * @inheritDoc
     */
    public function transform($data, string $to, array $context = []): object
    {
        if (!$data instanceof NSPage) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . NSPage::class);
        }
        $output = new NSPageOutput();
        $output->images = $data->getImages();
        $output->content = $data->getContent();
        $output->nodeReferences = $data->getNodeReferencesSources();

        return $this->transformNodesSources($output, $data, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return NSPageOutput::class === $to && $data instanceof NSPage;
    }
}
