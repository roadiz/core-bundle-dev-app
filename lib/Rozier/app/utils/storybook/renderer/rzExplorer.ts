import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzElement } from '~/utils/component-renderer/rzElement'
import { rzInputRenderer } from '~/utils/storybook/renderer/rzInput'

/** Same options as ui/components/rz_explorer.html.twig: the root attributes (translated labels) */
export type RzExplorerOptions = {
    attributes?: Record<string, string>
}

const DEFAULT_ATTRIBUTES: Record<string, string> = {
    'data-more-label': 'More entities',
    'data-more-label-document': 'More documents',
    'data-more-label-node': 'More nodes',
    'data-more-label-node-type': 'More node types',
    'data-more-label-tag': 'More tags',
    'data-item-label': 'item',
    'data-items-label': 'items',
}

/** Shorthand over rzElement for the many class + attributes nodes of the shell */
function element(
    tag: string,
    className = '',
    attributes: Record<string, string> = {},
) {
    return rzElement({
        tag,
        attributes: className
            ? { class: className, ...attributes }
            : attributes,
    })
}

function filtersRenderer() {
    const aside = element('aside', 'rz-explorer__filters', {
        'data-filter-panel': '',
        'aria-label': 'Filter',
    })
    aside.hidden = true

    const header = element('div', 'rz-explorer__filters-header')
    const count = element('span', 'rz-explorer__filters-count')
    count.append(
        element('span', '', { 'data-item-count': '' }),
        ' ',
        element('span', '', { 'data-item-count-label': '' }),
    )
    header.append(
        count,
        rzButtonRenderer({
            iconClass: 'rz-icon-ri--close-line',
            emphasis: 'tertiary',
            size: 'xs',
            attributes: {
                type: 'button',
                'aria-label': 'Close',
                'data-action': 'toggle-filters',
            },
        }),
    )

    const list = element('ul', 'rz-explorer__filters-list', {
        'data-filter-list': '',
    })
    const seeAll = element('li', 'rz-explorer__filters-item')
    seeAll.append(
        rzButtonRenderer({
            label: 'See all',
            iconClass: 'rz-icon-ri--list-unordered',
            emphasis: 'tertiary',
            size: 'sm',
            selected: true,
            attributes: {
                class: 'rz-explorer__filter',
                type: 'button',
                'aria-current': 'true',
                'data-action': 'reset-filter',
            },
        }),
    )
    list.append(seeAll)

    const inner = element('div', 'rz-explorer__filters-inner')
    inner.append(header, list)
    aside.append(inner)
    return aside
}

function mainRenderer() {
    const main = element('div', 'rz-explorer__main')

    const loader = element('div', 'rz-explorer__loader', {
        'data-loader': '',
        role: 'status',
    })
    loader.hidden = true
    const loaderLabel = element('span', 'rz-visually-hidden')
    loaderLabel.textContent = 'Loading'
    loader.append(
        element('span', 'rz-spinner rz-spinner--lg', { 'aria-hidden': 'true' }),
        loaderLabel,
    )

    const loadMore = rzButtonRenderer({
        emphasis: 'primary',
        attributes: {
            class: 'rz-explorer__load-more',
            type: 'button',
            'data-action': 'load-more',
        },
    })
    loadMore.hidden = true
    loadMore.append(
        element('span', 'rz-button__label', { 'data-load-more-label': '' }),
        element('span', 'rz-button__icon rz-icon-ri--add-line'),
        element('span', 'rz-button__icon rz-spinner'),
    )

    const pagination = element('div', 'rz-explorer__pagination', {
        'data-pagination': '',
    })
    pagination.hidden = true

    const tagCreator = element('div', 'rz-explorer__tag-creator', {
        'data-tag-creator': '',
    })
    tagCreator.hidden = true
    const tagCreatorInfos = element('div', 'rz-explorer__tag-creator-infos')
    tagCreatorInfos.textContent = 'Create this tag'
    const createTag = rzButtonRenderer({
        iconClass: 'rz-icon-ri--add-line',
        emphasis: 'primary',
        attributes: { type: 'button', 'data-action': 'create-tag' },
    })
    createTag.prepend(
        element('span', 'rz-button__label', { 'data-tag-name': '' }),
    )
    tagCreator.append(tagCreatorInfos, createTag)

    main.append(
        loader,
        element('ul', 'rz-explorer__list', { 'data-list': '' }),
        loadMore,
        pagination,
        tagCreator,
    )
    return main
}

/** Shell of rz-explorer, as rendered by Twig; RzExplorer.ts fills the items and filters once opened */
export function rzExplorerRenderer(options: RzExplorerOptions = {}) {
    const root = element('rz-explorer', '', {
        ...DEFAULT_ATTRIBUTES,
        ...options.attributes,
    })
    root.hidden = true

    const panel = element('div', 'rz-explorer', { 'data-panel': '' })
    const inner = element('div', 'rz-explorer__inner')

    const header = element('div', 'rz-explorer__header')
    const filterToggle = rzButtonRenderer({
        iconClass: 'rz-icon-ri--folders-line',
        attributes: {
            type: 'button',
            'aria-label': 'Filter',
            'data-action': 'toggle-filters',
            'data-filter-toggle': '',
        },
    })
    filterToggle.hidden = true

    const search = element('form', 'rz-explorer__search', {
        action: '#',
        method: 'POST',
        role: 'search',
    })
    search.append(
        rzInputRenderer({
            type: 'search',
            name: 'searchTerms',
            id: 'rz-explorer-search',
            placeholder: 'Search',
            attributes: {
                autocomplete: 'off',
                'aria-label': 'Search',
                'data-search': 'true',
            },
        }),
    )

    header.append(
        filterToggle,
        search,
        rzButtonRenderer({
            iconClass: 'rz-icon-ri--close-line',
            emphasis: 'secondary',
            attributes: {
                type: 'button',
                'aria-label': 'Close',
                'data-action': 'close',
            },
        }),
    )

    const body = element('div', 'rz-explorer__body')
    body.append(filtersRenderer(), mainRenderer())

    inner.append(header, body)
    panel.append(inner)
    root.append(panel)

    return root
}
