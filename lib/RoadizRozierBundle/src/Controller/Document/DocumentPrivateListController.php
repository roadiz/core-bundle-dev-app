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

    public function prepareBaseAssignation(): static
    {
        parent::prepareBaseAssignation();

        $this->assignation['pageTitle'] = 'private_documents';
        $this->assignation['displayPrivateDocuments'] = true;

        return $this;
    }
}
