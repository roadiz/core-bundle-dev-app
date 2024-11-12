<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Document;

class DocumentCollectionTransformer extends EntityCollectionTransformer
{
    public function __construct(ObjectManager $manager, bool $asCollection = false)
    {
        parent::__construct($manager, Document::class, $asCollection);
    }
}
