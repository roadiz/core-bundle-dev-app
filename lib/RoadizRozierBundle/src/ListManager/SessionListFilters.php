<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\ListManager;

use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SessionListFilters
{
    private string $sessionIdentifier;
    private int $defaultItemsParPage;

    /**
     * @param string $sessionIdentifier
     * @param int $defaultItemsParPage
     */
    public function __construct(string $sessionIdentifier, int $defaultItemsParPage = 20)
    {
        $this->sessionIdentifier = $sessionIdentifier;
        $this->defaultItemsParPage = $defaultItemsParPage;
    }

    /**
     * Handle item_per_page filter form session or from request query.
     *
     * @param Request $request
     * @param EntityListManagerInterface $listManager
     */
    public function handleItemPerPage(Request $request, EntityListManagerInterface $listManager): void
    {
        /*
         * Check if item_per_page is available from session
         */
        if (
            $request->hasSession() &&
            $request->getSession()->has($this->sessionIdentifier) &&
            $request->getSession()->get($this->sessionIdentifier) > 0 &&
            (!$request->query->has('item_per_page') ||
                $request->query->get('item_per_page') < 1)
        ) {
            /*
             * Item count is in session
             */
            $request->query->set('item_per_page', intval($request->getSession()->get($this->sessionIdentifier)));
            $listManager->setItemPerPage(intval($request->getSession()->get($this->sessionIdentifier)));
        } elseif (
            $request->query->has('item_per_page') &&
            $request->query->get('item_per_page') > 0
        ) {
            /*
             * Item count is in query, save it in session
             */
            $request->getSession()->set($this->sessionIdentifier, intval($request->query->get('item_per_page')));
            $listManager->setItemPerPage(intval($request->query->get('item_per_page')));
        } else {
            $listManager->setItemPerPage($this->defaultItemsParPage);
        }
    }
}
