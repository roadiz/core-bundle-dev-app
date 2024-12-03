<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Document;

use Symfony\Component\HttpFoundation\Request;

class DocumentPrivateListController extends DocumentPublicListController
{
    protected function getPreFilters(Request $request): array
    {
        return [
            'private' => true,
            'raw' => false,
        ];
    }

    public function getAssignation(): array
    {
        return [
            'pageTitle' => 'private_documents',
            'displayPrivateDocuments' => true,
        ];
    }
}
