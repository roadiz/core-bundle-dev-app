<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use RZ\Roadiz\CoreBundle\Doctrine\SchemaUpdater;
use RZ\Roadiz\CoreBundle\Message\UpdateDoctrineSchemaMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateDoctrineSchemaMessageHandler implements MessageHandlerInterface
{
    private SchemaUpdater $schemaUpdater;

    public function __construct(SchemaUpdater $schemaUpdater)
    {
        $this->schemaUpdater = $schemaUpdater;
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
