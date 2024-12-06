<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use RZ\Roadiz\CoreBundle\Doctrine\SchemaUpdater;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateDoctrineSchemaMessageHandler
{
    public function __construct(private SchemaUpdater $schemaUpdater)
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(UpdateDoctrineSchemaMessage $message): void
    {
        $this->schemaUpdater->updateNodeTypesSchema();
        $this->schemaUpdater->clearAllCaches();
    }
}
