<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use RZ\Roadiz\Core\AbstractEntities\LeafInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Single source of truth for back-office section roots, the first entry of every breadcrumb trail.
 *
 * Section head templates read it through the `breadcrumb_root()` Twig function; the few pages that
 * cannot go through their section head (confirmation pages, sections without a head) call it too,
 * instead of repeating the label and the route by hand.
 *
 * This table covers the sections Rozier itself ships. A project adding its own back-office section
 * writes the entry inline in its template — one place, explicit, no indirection — because a project
 * has no duplication to factor out. See docs/extensions/custom_backoffice_entry.md.
 */
final readonly class BreadcrumbRoots
{
    /**
     * Section name => [translation key, listing route name].
     *
     * @var array<string, array{string, string}>
     */
    private const array ROOTS = [
        'attributes' => ['attributes', 'attributesHomePage'],
        'attributeGroups' => ['attribute_groups', 'attributeGroupsHomePage'],
        'customForms' => ['customForms', 'customFormsHomePage'],
        'documents' => ['documents', 'documentsHomePage'],
        'folders' => ['folders', 'foldersHomePage'],
        'groups' => ['groups', 'groupsHomePage'],
        'nodes' => ['all.nodes', 'nodesHomePage'],
        'nodeTypes' => ['nodeType', 'nodeTypesHomePage'],
        'realms' => ['realms', 'realmsHomePage'],
        'redirections' => ['manage.redirections', 'redirectionsHomePage'],
        'settingGroups' => ['settingGroups', 'settingGroupsHomePage'],
        'settings' => ['settings', 'settingsHomePage'],
        'tags' => ['tags', 'tagsHomePage'],
        'translations' => ['translations', 'translationsHomePage'],
        'users' => ['users', 'usersHomePage'],
        'webhooks' => ['webhooks', 'webhooksHomePage'],
    ];

    public function __construct(
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * The whole path down to a tree entity: section root, its ancestors, then the entity itself.
     *
     * Confirmation pages show it as the trail above their own title, so deleting a deep folder
     * reads "Folders › Invoices › 2026 › Delete 2026" instead of losing the item and its ancestry.
     *
     * @return array<array<string, string>|LeafInterface>
     */
    public function trailTo(string $section, LeafInterface $item): array
    {
        return [$this->get($section), ...$item->getParents(), $item];
    }

    /**
     * @return array{label: string, url: string, route: string}
     *                                                          a breadcrumb entry, plus the route name so a head can tell whether the current page is that root
     */
    public function get(string $section): array
    {
        if (!isset(self::ROOTS[$section])) {
            throw new \InvalidArgumentException(\sprintf('Unknown breadcrumb root "%s", expected one of: %s.', $section, implode(', ', array_keys(self::ROOTS))));
        }

        [$label, $route] = self::ROOTS[$section];

        return [
            'label' => $this->translator->trans($label),
            'url' => $this->urlGenerator->generate($route),
            'route' => $route,
        ];
    }
}
