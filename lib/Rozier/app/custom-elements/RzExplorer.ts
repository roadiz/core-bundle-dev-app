import { debounce } from 'lodash'
import { getExplorerItems } from '~/api/ExplorerApi'
import { getFilters } from '~/api/FilterExplorerApi'
import { createTag } from '~/api/TagApi'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import {
    rzCardRenderer,
    type RzCardOptions,
} from '~/utils/component-renderer/rzCard'
import type { RzDrawerItem } from '~/utils/drawer/types'

export const SHOW_EXPLORER_EVENT = 'show-explorer'
export const ADD_DRAWER_ITEM_EVENT = 'add-drawer-item'

export type ShowExplorerDetail = {
    id?: string
    acceptEntity?: string
    filters?: Record<string, unknown> | null
}

type ExplorerItem = RzDrawerItem & {
    name?: string
    thumbnail?: { url?: string; publicUrl?: string } | string | null
}

type ExplorerFilters = {
    nextPage?: number
    itemCount?: number
    [key: string]: unknown
}

type FilterItem = {
    id: number
    name: string
    children?: FilterItem[]
}

type EntityConfig = {
    filterIconClass?: string
    filterItemIcons?: { normal: string; active: string }
    tagCreator?: boolean
}

const ENTITY_CONFIGS: Record<string, EntityConfig> = {
    document: {
        filterIconClass: 'rz-icon-ri--folders-line',
        filterItemIcons: {
            normal: 'rz-icon-ri--folder-line',
            active: 'rz-icon-ri--folder-fill',
        },
    },
    node: {
        filterIconClass: 'rz-icon-ri--price-tag-3-line',
        filterItemIcons: {
            normal: 'rz-icon-ri--price-tag-3-line',
            active: 'rz-icon-ri--price-tag-3-line',
        },
    },
    tag: {
        filterIconClass: 'rz-icon-ri--price-tag-3-line',
        filterItemIcons: {
            normal: 'rz-icon-ri--price-tag-3-line',
            active: 'rz-icon-ri--price-tag-3-line',
        },
        tagCreator: true,
    },
}

const SEARCH_DEBOUNCE = 450

/** Single selection among the filters: the current one is marked, not toggled */
function setFilterSelected(button: HTMLElement, selected: boolean) {
    button.classList.toggle('rz-button--selected', selected)
    if (selected) {
        button.setAttribute('aria-current', 'true')
    } else {
        button.removeAttribute('aria-current')
    }
}

/**
 * Explorer panel: lets a drawer (`rz-drawer`) pick entities (documents, nodes, node types, tags,
 * custom forms, joins and explorer provider items), placed once in the shared layout.
 *
 * Twig renders the shell (header, filter panel, load more, tag creator, translated labels);
 * this element fetches and renders the items and the filter tree, which only exist after the
 * user opened the explorer.
 *
 * Contract:
 * - opens on `show-explorer` on `document`, `detail` = `ShowExplorerDetail` (`id` of the drawer,
 *   `acceptEntity`, `filters` sent to the entity endpoint)
 * - answers `add-drawer-item` on `document`, `detail` = `{ drawerId, item }`, when an item is
 *   picked or a tag is created
 *
 * Expected children:
 * - `[data-panel]`: the explorer panel, `[data-filter-panel]`: the filter column inside it,
 *   which widens the panel when shown (`rz-explorer.css`)
 * - `[data-search]`: the search input; `[data-list]`: the item list; `[data-loader]`
 * - `[data-action="close|toggle-filters|load-more|reset-filter|create-tag"]`; `[data-filter-toggle]`:
 *   the header button toggling the filter panel (hidden for entities without filters)
 * - `[data-filter-list]`: receives the filter tree; `[data-item-count]`, `[data-item-count-label]`
 * - `[data-load-more-label]`, `[data-pagination]`, `[data-tag-creator]`, `[data-tag-name]`
 *
 * Attributes: `data-more-label-<entity>` and `data-more-label` (fallback) for the load more
 * button, `data-item-label` / `data-items-label` for the filter panel count.
 *
 * Closes on Escape, on the close button, on a pointer down outside the panels and on page change.
 */
export default class RzExplorer extends HTMLElement {
    private entity = ''
    private drawerId = ''
    private preFilters: ShowExplorerDetail['filters'] = null
    private filters: ExplorerFilters = {}
    private items: ExplorerItem[] = []
    private selectedFilter: FilterItem | null = null
    private filterItems: FilterItem[] = []
    private requestId = 0

    private debouncedSearch = debounce(() => this.search(), SEARCH_DEBOUNCE)

    constructor() {
        super()

        this.onShowExplorer = this.onShowExplorer.bind(this)
        this.onClick = this.onClick.bind(this)
        this.onSearchInput = this.onSearchInput.bind(this)
        this.onSearchKeydown = this.onSearchKeydown.bind(this)
        this.onDocumentKeyup = this.onDocumentKeyup.bind(this)
        this.onDocumentPointerDown = this.onDocumentPointerDown.bind(this)
        this.close = this.close.bind(this)
    }

    get isOpen() {
        return !this.hidden
    }

    get config(): EntityConfig {
        return ENTITY_CONFIGS[this.entity] || {}
    }

    get searchInput() {
        return this.querySelector<HTMLInputElement>('[data-search]')
    }

    get filterPanel() {
        return this.querySelector<HTMLElement>('[data-filter-panel]')
    }

    get isFilterPanelOpen() {
        return !!this.filterPanel && !this.filterPanel.hidden
    }

    child<T extends HTMLElement = HTMLElement>(name: string) {
        return this.querySelector<T>(`[data-${name}]`)
    }

    action(name: string) {
        return this.querySelector<HTMLElement>(`[data-action="${name}"]`)
    }

    // OPEN / CLOSE
    onShowExplorer(event: Event) {
        const detail = (event as CustomEvent<ShowExplorerDetail>).detail

        if (!detail?.acceptEntity) return

        this.open(detail)
    }

    open(detail: ShowExplorerDetail) {
        this.reset()

        this.entity = detail.acceptEntity || ''
        this.drawerId = detail.id || ''
        this.preFilters = detail.filters || null

        const filterToggle = this.child('filter-toggle')
        if (filterToggle) {
            filterToggle.hidden = !this.config.filterIconClass
            const icon = filterToggle.querySelector('.rz-button__icon')
            if (icon)
                icon.className = `rz-button__icon ${this.config.filterIconClass || ''}`
        }

        const moreLabel = this.child('load-more-label')
        if (moreLabel) {
            moreLabel.textContent =
                this.dataset[this.datasetKey(`more-label-${this.entity}`)] ||
                this.dataset.moreLabel ||
                ''
        }

        this.hidden = false
        this.searchInput?.focus()

        this.search()
    }

    close() {
        if (!this.isOpen) return

        this.hidden = true
        this.reset()
    }

    reset() {
        this.requestId++
        this.debouncedSearch.cancel()
        this.filters = {}
        this.items = []
        this.selectedFilter = null
        this.filterItems = []

        if (this.searchInput) this.searchInput.value = ''
        if (this.filterPanel) this.filterPanel.hidden = true

        this.renderItems()
        this.renderFilters()
        this.renderFooter()
    }

    // SEARCH
    async search({ more = false }: { more?: boolean } = {}) {
        const requestId = ++this.requestId
        const loader = this.child('loader')
        const loadMore = this.action('load-more')

        if (more) {
            loadMore?.classList.add('rz-explorer__load-more--loading')
        } else if (loader) {
            loader.hidden = false
        }

        try {
            const result = await getExplorerItems({
                entity: this.entity,
                searchTerms: this.searchInput?.value || '',
                preFilters: this.preFilters,
                filters: this.filters,
                filterExplorerSelection: this.selectedFilter,
                moreData: more,
            })

            // A newer search or a close happened in the meantime
            if (requestId !== this.requestId) return

            const items: ExplorerItem[] = result?.items || []
            this.items = more ? [...this.items, ...items] : items
            this.filters = result?.filters || {}

            this.renderItems(more ? items : undefined)
        } catch (error) {
            console.error(error)
        } finally {
            if (requestId === this.requestId) {
                if (loader) loader.hidden = true
                loadMore?.classList.remove('rz-explorer__load-more--loading')
                this.renderFooter()
            }
        }
    }

    onSearchInput() {
        this.debouncedSearch()
    }

    onSearchKeydown(event: KeyboardEvent) {
        if (event.key !== 'Enter') return

        event.preventDefault()
        this.debouncedSearch.cancel()
        this.search()
    }

    resetSearchTerms() {
        if (!this.searchInput || this.searchInput.value === '') return

        this.searchInput.value = ''
        this.debouncedSearch.cancel()
        this.search()
    }

    // ITEMS
    renderItems(appended?: ExplorerItem[]) {
        const list = this.child('list')
        if (!list) return

        if (!appended) list.replaceChildren()

        const fragment = document.createDocumentFragment()
        ;(appended || this.items).forEach((item) => {
            fragment.appendChild(this.createItemElement(item))
        })
        list.appendChild(fragment)
    }

    createItemElement(item: ExplorerItem) {
        const options: RzCardOptions = {
            tag: 'li',
            buttonGroupTop: {
                buttons: [
                    {
                        iconClass: 'rz-icon-ri--add-large-line',
                        emphasis: 'primary',
                        attributes: { type: 'button' },
                    },
                ],
            },
        }

        if (this.entity === 'document') {
            if (item.isPrivate) {
                options.badge = {
                    iconClass: 'rz-icon-ri--lock-2-line',
                    size: 'md',
                }
            } else if (item.isPdf) {
                options.badge = {
                    iconClass: 'rz-icon-ri--file-pdf-2-line',
                    size: 'md',
                }
            } else {
                options.image = {
                    src: item.thumbnail80 || '',
                    alt: item.classname || 'Thumbnail',
                    width: 110,
                    height: 94,
                }
            }
        } else {
            options.overtitle = item.classname
            options.title = item.displayable

            const thumbnailUrl = this.getThumbnailUrl(item)
            if (thumbnailUrl) {
                options.image = {
                    src: thumbnailUrl,
                    alt: item.displayable || 'Thumbnail',
                    width: 110,
                    height: 94,
                }
            }
        }

        const element = rzCardRenderer(options)
        if (this.entity === 'document' && item.classname) {
            element.title = item.classname
        }

        // The whole card picks the item, its button included
        element.addEventListener('click', (event) => {
            event.preventDefault()
            this.addItem(item)
        })

        return element
    }

    getThumbnailUrl(item: ExplorerItem) {
        if (typeof item.thumbnail === 'string') return item.thumbnail

        return item.thumbnail?.url || item.thumbnail?.publicUrl || null
    }

    addItem(item: ExplorerItem) {
        document.dispatchEvent(
            new CustomEvent(ADD_DRAWER_ITEM_EVENT, {
                detail: { item, drawerId: this.drawerId },
            }),
        )
    }

    // FOOTER: load more, pagination, filter count, tag creator
    renderFooter() {
        const { nextPage, itemCount } = this.filters
        const hasItemCount = typeof itemCount === 'number'

        const loadMore = this.action('load-more')
        if (loadMore) loadMore.hidden = !(nextPage && nextPage > 1)

        const pagination = this.child('pagination')
        if (pagination) {
            pagination.hidden = !(this.items.length && itemCount)
            pagination.textContent = `${this.items.length} / ${itemCount || 0}`
        }

        const count = this.child('item-count')
        const countLabel = this.child('item-count-label')
        if (count) count.textContent = hasItemCount ? String(itemCount) : ''
        if (countLabel) {
            countLabel.textContent = hasItemCount
                ? (itemCount > 1
                      ? this.dataset.itemsLabel
                      : this.dataset.itemLabel) || ''
                : ''
        }

        this.renderTagCreator()
    }

    renderTagCreator() {
        const tagCreator = this.child('tag-creator')
        if (!tagCreator) return

        const searchTerms = (this.searchInput?.value || '').trim()
        // Tag explorer items carry their name in `displayable`
        const isTagExisting = this.items.some(
            (item) =>
                (item.name ?? item.displayable)?.toLowerCase() ===
                searchTerms.toLowerCase(),
        )

        tagCreator.hidden =
            !this.config.tagCreator || !searchTerms || isTagExisting

        const tagName = this.child('tag-name')
        if (tagName) tagName.textContent = searchTerms
    }

    async onCreateTag() {
        const tagName = (this.searchInput?.value || '').trim()
        if (!tagName) return

        try {
            const tag = await createTag({ tagName })
            this.addItem(tag)
            this.resetSearchTerms()
        } catch (error) {
            console.error(error)
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message:
                            error instanceof Error
                                ? error.message
                                : String(error),
                        status: 'danger',
                    },
                }),
            )
        }
    }

    // FILTERS
    async toggleFilters() {
        const panel = this.filterPanel
        if (!panel) return

        if (!panel.hidden) {
            panel.hidden = true
            return
        }

        panel.hidden = false
        this.resetSearchTerms()

        try {
            const result = await getFilters({ entity: this.entity })
            this.filterItems = result?.items || []
        } catch (error) {
            console.error(error)
            this.filterItems = []
        }

        this.renderFilters()
    }

    selectFilter(item: FilterItem | null) {
        this.selectedFilter = item
        // A new filter starts back from the first page
        this.filters = {}
        this.renderFilters()
        this.search()
    }

    renderFilters() {
        // The filter list keeps its Twig-rendered head (count, "see all"); filter items follow it
        const list = this.child('filter-list')
        if (list) {
            // Rebuilding the buttons drops the focus: give it back to the same filter
            const focusedId =
                document.activeElement instanceof HTMLElement &&
                list.contains(document.activeElement)
                    ? document.activeElement.dataset.filterId
                    : undefined

            list.querySelectorAll(':scope > [data-filter-item]').forEach(
                (item) => item.remove(),
            )
            list.append(
                ...this.filterItems.map((item) =>
                    this.createFilterElement(item),
                ),
            )

            if (focusedId) {
                list.querySelector<HTMLElement>(
                    `[data-filter-id="${CSS.escape(focusedId)}"]`,
                )?.focus()
            }
        }

        const resetFilter = this.action('reset-filter')
        if (resetFilter) setFilterSelected(resetFilter, !this.selectedFilter)

        this.child('filter-toggle')?.classList.toggle(
            'rz-button--pill',
            !!this.selectedFilter,
        )
    }

    createFilterElement(item: FilterItem): HTMLElement {
        const icons = this.config.filterItemIcons || { normal: '', active: '' }
        const isCurrent = this.selectedFilter?.id === item.id

        const li = document.createElement('li')
        li.classList.add('rz-explorer__filters-item')
        li.dataset.filterItem = ''

        const button = rzButtonRenderer({
            emphasis: 'tertiary',
            size: 'sm',
            iconClass: isCurrent ? icons.active : icons.normal,
            attributes: {
                type: 'button',
                class: 'rz-explorer__filter',
                'data-filter-id': String(item.id),
            },
        })
        setFilterSelected(button, isCurrent)
        // Folder and tag names are user content: text, never HTML
        const label = document.createElement('span')
        label.className = 'rz-button__label'
        label.textContent = item.name
        button.prepend(label)
        button.addEventListener('click', (event) => {
            event.preventDefault()
            this.selectFilter(item)
        })

        li.append(button)

        if (item.children?.length) {
            const children = document.createElement('ul')
            children.classList.add('rz-explorer__filters-list')
            children.append(
                ...item.children.map((child) =>
                    this.createFilterElement(child),
                ),
            )
            li.append(children)
        }

        return li
    }

    // EVENTS
    onClick(event: MouseEvent) {
        const target = (event.target as HTMLElement).closest<HTMLElement>(
            '[data-action]',
        )
        if (!target || !this.contains(target)) return

        switch (target.dataset.action) {
            case 'close':
                event.preventDefault()
                this.close()
                break
            case 'toggle-filters':
                event.preventDefault()
                this.toggleFilters()
                break
            case 'load-more':
                event.preventDefault()
                this.search({ more: true })
                break
            case 'reset-filter':
                event.preventDefault()
                this.selectFilter(null)
                break
            case 'create-tag':
                event.preventDefault()
                this.onCreateTag()
                break
        }
    }

    onDocumentKeyup(event: KeyboardEvent) {
        if (event.key === 'Escape') this.close()
    }

    // A pointer down outside the panel (filter column included) closes the explorer
    // (the opening click already happened)
    onDocumentPointerDown(event: PointerEvent) {
        if (!this.isOpen) return

        const path = event.composedPath()
        const panel = this.child('panel')
        if (panel && path.includes(panel)) return

        // Leave the drawer button that opened the explorer toggle it as before
        const drawer = this.drawerId
            ? document.getElementById(this.drawerId)
            : null
        if (drawer && path.includes(drawer)) return

        this.close()
    }

    datasetKey(name: string) {
        return name.replace(/-([a-z])/g, (_, letter: string) =>
            letter.toUpperCase(),
        )
    }

    connectedCallback() {
        document.addEventListener(SHOW_EXPLORER_EVENT, this.onShowExplorer)
        document.addEventListener('keyup', this.onDocumentKeyup)
        document.addEventListener('pointerdown', this.onDocumentPointerDown)
        window.addEventListener('pagechange', this.close)
        this.addEventListener('click', this.onClick)
        this.searchInput?.addEventListener('input', this.onSearchInput)
        this.searchInput?.addEventListener('keydown', this.onSearchKeydown)
    }

    disconnectedCallback() {
        document.removeEventListener(SHOW_EXPLORER_EVENT, this.onShowExplorer)
        document.removeEventListener('keyup', this.onDocumentKeyup)
        document.removeEventListener('pointerdown', this.onDocumentPointerDown)
        window.removeEventListener('pagechange', this.close)
        this.removeEventListener('click', this.onClick)
        this.searchInput?.removeEventListener('input', this.onSearchInput)
        this.searchInput?.removeEventListener('keydown', this.onSearchKeydown)
        this.debouncedSearch.cancel()
    }
}
