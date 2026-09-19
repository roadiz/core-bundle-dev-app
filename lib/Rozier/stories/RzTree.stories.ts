import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzBadgeRenderer } from '~/utils/component-renderer/rzBadge'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'
import { rzDropdownRenderer } from '~/utils/storybook/renderer/rzDropDown'
import { rzNodeIconRenderer } from '~/utils/storybook/renderer/rzNodeIcon'

type Item = {
    label: string
    iconClass?: string
    icon?: HTMLElement
    href?: string
    expanded?: boolean
    children?: Item[]
    actions?: HTMLElement[]
    /** Stack tree only: tags fold into a popover past MAX_VISIBLE_TAGS or when the row is narrow. */
    tags?: string[]
}

/** Mirrors `visibleCount` in widgets/nodeTree/singleNode.html.twig — keep both in step. */
const MAX_VISIBLE_TAGS = 6

export type Args = {
    items: Item[]
    iconColor?: string
}

const COMPONENT_CLASS_NAME = 'rz-tree'
const meta: Meta<Args> = {
    title: 'Components/Tree',
    tags: ['autodocs'],
    args: {
        iconColor: '',
    },
}

export default meta
type Story = StoryObj<Args>

function itemNodeRenderer(item: Item) {
    const hasChildren = item.children && item.children.length > 0
    const tag = item.href ? 'a' : 'div'

    const node = document.createElement(tag)
    node.setAttribute('role', 'treeitem')
    node.setAttribute('aria-expanded', item.expanded ? 'true' : 'false')
    node.classList.add(`${COMPONENT_CLASS_NAME}__item__node`)

    const innerEl = document.createElement('div')
    innerEl.classList.add(`${COMPONENT_CLASS_NAME}__item__node__inner`)
    node.appendChild(innerEl)

    const handle = document.createElement('span')
    handle.classList.add(`${COMPONENT_CLASS_NAME}__item__handle`)
    handle.classList.add('rz-icon-ri--draggable')
    innerEl.appendChild(handle)

    // Icon slot - use custom element if provided, otherwise use icon class
    if (item.icon) {
        item.icon.classList.add(`${COMPONENT_CLASS_NAME}__item__icon`)
        innerEl.appendChild(item.icon)
    } else if (item.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${COMPONENT_CLASS_NAME}__item__icon`)
        icon.classList.add(item.iconClass)
        innerEl.appendChild(icon)
    }

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__item__label`)
    label.textContent = item.label
    innerEl.appendChild(label)

    if (item.tags && item.tags.length > 0) {
        innerEl.appendChild(tagsRenderer(item.tags))
    }

    if (hasChildren) {
        const expandButton = rzButtonRenderer({
            tag: 'span',
            iconClass: 'rz-icon-ri--arrow-down-s-line',
            emphasis: 'tertiary',
            size: 'xs',
        })
        expandButton.classList.add(
            `${COMPONENT_CLASS_NAME}__item__expand-button`,
        )

        innerEl.appendChild(expandButton)
    }

    // Actions slot - populated from item.actions
    if (item.actions && item.actions.length > 0) {
        const actionsSlot = document.createElement('div')
        actionsSlot.classList.add(`${COMPONENT_CLASS_NAME}__item__actions`)
        item.actions.forEach((action) => actionsSlot.appendChild(action))
        innerEl.appendChild(actionsSlot)
    }

    return node
}

/** Mirrors what rz_overflow_list.html.twig emits for singleNode.html.twig with `responsive`. */
function tagsRenderer(tags: string[]) {
    const badge = (tag: string) =>
        rzBadgeRenderer({ tag: 'rz-badge', label: tag, size: 'sm' })
    const inline = tags.slice(0, MAX_VISIBLE_TAGS)
    const folded = tags.slice(MAX_VISIBLE_TAGS)

    const wrapper = document.createElement('div')
    wrapper.className = `rz-overflow-list ${COMPONENT_CLASS_NAME}__item__tags`
    inline.forEach((tag) => wrapper.appendChild(badge(tag)))

    const list = document.createElement('div')
    list.className = 'rz-dropdown__list rz-overflow-list__list'
    folded.forEach((tag) => list.appendChild(badge(tag)))
    const content = document.createElement('div')
    content.className = `rz-dropdown rz-overflow-list__popover ${COMPONENT_CLASS_NAME}__item__tags__popover`
    content.appendChild(list)

    const { popover } = rzPopoverRenderer({
        targetElement: {
            element: rzButtonRenderer({
                iconClass: 'rz-icon-ri--price-tag-3-line',
                emphasis: 'secondary',
                size: 'sm',
                label: folded.length ? String(folded.length) : undefined,
                attributes: {
                    type: 'button',
                    'aria-label': 'More tags',
                    class: 'rz-overflow-list__button',
                },
            }),
        },
        popoverElement: {
            element: content,
            id: `rz-tree-tags-${Math.random().toString(36).slice(2, 8)}`,
        },
        offset: 8,
    })
    popover.setAttribute('overflow-responsive', '')
    popover.setAttribute('overflow-inline', String(inline.length))
    popover.setAttribute('overflow-inline-side', 'before')
    popover.setAttribute('overflow-count', '')
    popover.hidden = folded.length === 0
    wrapper.appendChild(popover)

    return wrapper
}

function itemRenderer(item: Item) {
    const ITEM_LIST_CLASS = 'rz-tree-item'

    const li = document.createElement('li', { is: ITEM_LIST_CLASS })
    li.setAttribute('is', ITEM_LIST_CLASS)
    li.classList.add(`${COMPONENT_CLASS_NAME}__item`)

    const content = itemNodeRenderer(item)
    li.appendChild(content)

    if (item.children) {
        li.classList.add(`${COMPONENT_CLASS_NAME}__item--parent`)

        const list = listRenderer(item.children)
        list.setAttribute('role', 'group')
        li.appendChild(list)
    } else {
        li.classList.add(`${COMPONENT_CLASS_NAME}__item--end`)
    }

    return li
}

function listRenderer(items: Item[]) {
    const element = document.createElement('ul')

    element.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    items.forEach((item) => {
        const itemEl = itemRenderer(item)
        element.appendChild(itemEl)
    })

    return element
}

function rootRenderer(args: Args) {
    const element = document.createElement('rz-tree')
    element.classList.add(COMPONENT_CLASS_NAME)

    const list = listRenderer(args.items)
    list.setAttribute('role', 'tree')
    element.appendChild(list)

    if (args.iconColor) {
        element.style.setProperty('--rz-tree-icon-color', args.iconColor)
    }

    return element
}

export const Default: Story = {
    args: {
        items: [
            {
                label: 'Menu 1',
                iconClass: 'rz-icon-ri--home-2-fill',
                expanded: true,
                children: [
                    {
                        label: 'item 1.1',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                    {
                        label: 'item 1.2',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 1.3',
                        expanded: true,
                        iconClass: 'rz-icon-rz--status-published-fill',
                        children: [
                            {
                                label: 'item 1.3.1',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                            },
                            {
                                label: 'item 1.3.2',
                                iconClass: 'rz-icon-rz--status-published-fill',
                                children: [
                                    {
                                        label: 'item 1.3.2.1',
                                        iconClass:
                                            'rz-icon-rz--status-draft-fill',
                                    },
                                ],
                            },
                        ],
                    },
                    {
                        label: 'item 1.4',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                ],
            },
            {
                label: 'Page 1',
                iconClass: 'rz-icon-rz--status-published-fill',
            },
            {
                label: 'Page 2',
                iconClass: 'rz-icon-rz--status-published-fill',
            },
            {
                label: 'Menu 2',
                iconClass: 'rz-icon-rz--status-draft-fill',
                children: [
                    {
                        label: 'item 2.1',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                    {
                        label: 'item 2.2',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                ],
            },
            {
                label: 'Menu 3',
                iconClass: 'rz-icon-rz--status-draft-fill',
                children: [
                    {
                        label: 'item 3.1',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        children: [
                            {
                                label: 'item 3.1.1',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                            },
                        ],
                    },
                ],
            },
            {
                label: 'Menu 4',
                iconClass: 'rz-icon-rz--status-draft-fill',
                children: [
                    {
                        label: 'item 1.1',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 1.2',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                    {
                        label: 'item 1.3',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        children: [
                            {
                                label: 'item 1.3.1',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                            },
                            {
                                label: 'item 1.3.2',
                                iconClass: 'rz-icon-rz--status-published-fill',
                                children: [
                                    {
                                        label: 'item 1.3.2.1',
                                        iconClass:
                                            'rz-icon-rz--status-draft-fill',
                                    },
                                ],
                            },
                        ],
                    },
                ],
            },
        ],
    },
    render: (args) => {
        return rootRenderer(args)
    },
}

export const ChildNodes: Story = {
    args: {
        items: [
            {
                label: 'Menu 1',
                iconClass: 'rz-icon-rz--status-published-fill',
                expanded: true,
                children: [
                    {
                        label: 'item 1.1',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 1.3',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        expanded: true,
                        children: [
                            {
                                label: 'item 1.3.1',
                                iconClass: 'rz-icon-rz--status-published-fill',
                            },
                            {
                                label: 'item 1.3.2',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                                children: [
                                    {
                                        label: 'item 1.3.2.1',
                                        iconClass:
                                            'rz-icon-rz--status-published-fill',
                                    },
                                ],
                            },
                        ],
                    },
                    {
                        label: 'item 1.4',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                ],
            },
            { label: 'Page 2', iconClass: 'rz-icon-rz--status-published-fill' },
            {
                label: 'Menu 2',
                iconClass: 'rz-icon-rz--status-draft-fill',
                expanded: true,
                children: [
                    {
                        label: 'item 2.1',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 2.2',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                ],
            },
        ],
    },
    render: (args) => {
        const tree = rootRenderer(args)
        tree.classList.add(`${COMPONENT_CLASS_NAME}--child-nodes`)
        return tree
    },
}

/**
 * Generates a unique ID for contextual menu instances
 */
let contextualMenuCounter = 0
function generateContextualMenuId() {
    return `node-contextual-menu-${++contextualMenuCounter}`
}

/**
 * Creates a RzNodeTreeContextualMenu element with a popover trigger button
 */
function createContextualMenuAction(): HTMLElement {
    const contextualId = generateContextualMenuId()
    const popoverId = `${contextualId}-popover`

    // Create the contextual menu wrapper element
    const contextualMenu = document.createElement(
        'rz-node-tree-contextual-menu',
    )
    contextualMenu.classList.add('rz-node-contextual-menu')
    contextualMenu.setAttribute('id', contextualId)
    contextualMenu.setAttribute('popover-placement', 'bottom-end')
    // These paths won't work in Storybook but are included for demonstration
    contextualMenu.setAttribute('data-node-id', '1')
    contextualMenu.setAttribute('data-contextual-menu-path', '#')
    contextualMenu.setAttribute('data-node-status-path', '#')
    contextualMenu.setAttribute('data-node-duplicate-path', '#')
    contextualMenu.setAttribute('data-node-paste-path', '#')
    contextualMenu.setAttribute(
        'data-node-copied-trans',
        'Node copied to clipboard',
    )
    contextualMenu.setAttribute('data-node-edit-position-path', '#')

    // Create the trigger button
    const triggerButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--more-line',
        emphasis: 'tertiary',
        size: 'xs',
        attributes: {
            'aria-label': 'Show actions',
            popovertarget: popoverId,
            type: 'button',
        },
    })
    contextualMenu.appendChild(triggerButton)

    // Create the popover placeholder (content is fetched on open)
    const popoverPlaceholder = document.createElement('div')
    popoverPlaceholder.id = popoverId
    popoverPlaceholder.setAttribute('popover', '')
    popoverPlaceholder.setAttribute('data-popover-content-state', 'idle')
    popoverPlaceholder.setAttribute('data-contextual-menu-popover', '')

    // Use rzDropdownRenderer for popover content
    const dropdown = rzDropdownRenderer(
        {
            title: 'Actions',
            items: [
                [
                    {
                        tag: 'button',
                        iconClass: 'rz-icon-ri--file-copy-line',
                        label: 'Duplicate',
                        attributes: { command: '--duplicate' },
                    },
                    {
                        tag: 'button',
                        iconClass: 'rz-icon-ri--clipboard-line',
                        label: 'Copy',
                        attributes: { command: '--copy' },
                    },
                ],
                [
                    {
                        tag: 'button',
                        iconClass: 'rz-icon-ri--arrow-up-line',
                        label: 'Move to first',
                        attributes: { command: '--move-first' },
                    },
                    {
                        tag: 'button',
                        iconClass: 'rz-icon-ri--arrow-down-line',
                        label: 'Move to last',
                        attributes: { command: '--move-last' },
                    },
                ],
            ],
        },
        popoverPlaceholder,
    )
    dropdown.classList.add('rz-dropdown')

    popoverPlaceholder.setAttribute('data-popover-content-state', 'fetched')
    contextualMenu.appendChild(popoverPlaceholder)

    return contextualMenu
}

export const WithContextualMenu: Story = {
    args: {
        items: [
            {
                label: 'Home',
                iconClass: 'rz-icon-ri--home-2-fill',
                expanded: true,
                actions: [createContextualMenuAction()],
                children: [
                    {
                        label: 'About us',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Contact',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Services',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        expanded: true,
                        actions: [createContextualMenuAction()],
                        children: [
                            {
                                label: 'Web development',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                                actions: [createContextualMenuAction()],
                            },
                            {
                                label: 'Mobile apps',
                                iconClass: 'rz-icon-rz--status-published-fill',
                                actions: [createContextualMenuAction()],
                            },
                        ],
                    },
                ],
            },
            {
                label: 'Blog',
                iconClass: 'rz-icon-rz--status-draft-fill',
                actions: [createContextualMenuAction()],
            },
            {
                label: 'Products',
                iconClass: 'rz-icon-rz--status-published-fill',
                actions: [createContextualMenuAction()],
                children: [
                    {
                        label: 'Product A',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Product B',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        actions: [createContextualMenuAction()],
                    },
                ],
            },
        ],
    },
    render: (args) => {
        return rootRenderer(args)
    },
}

export const WithThumbnail: Story = {
    args: {
        items: [
            {
                label: 'Gallery',
                icon: rzNodeIconRenderer({
                    nodeId: '2',
                    status: 'published',
                    size: 'medium',
                    color: 'blue',
                }),
                expanded: true,
                children: [
                    {
                        label: 'Photo 1',
                        icon: rzNodeIconRenderer({
                            nodeId: '2',
                            size: 'medium',
                            status: 'draft',
                            color: 'salmon',
                        }),
                    },
                    {
                        label: 'Photo 2',
                        icon: rzNodeIconRenderer({
                            nodeId: '3',
                            size: 'medium',
                            status: 'published',
                        }),
                    },
                    {
                        label: 'Album',
                        icon: rzNodeIconRenderer({
                            nodeId: '4',
                            size: 'medium',
                            status: 'draft',
                        }),
                        expanded: true,
                        children: [
                            {
                                label: 'Photo 3',
                                icon: rzNodeIconRenderer({
                                    nodeId: '5',
                                    size: 'medium',
                                    status: 'published',
                                }),
                            },
                            {
                                label: 'Photo 4',
                                icon: rzNodeIconRenderer({
                                    nodeId: '6',
                                    size: 'medium',
                                    status: 'published',
                                }),
                            },
                        ],
                    },
                ],
            },
        ],
    },
    render: (args) => {
        const tree = rootRenderer(args)
        tree.classList.add(`${COMPONENT_CLASS_NAME}--child-nodes`)
        return tree
    },
}

const nextFrame = () => new Promise(requestAnimationFrame)

/**
 * Stack tree rows with tags. Drag the resize handle: tags fold into the popover one by one when
 * the row narrows, the title keeps its minimum width, the button counts the folded ones.
 */
export const WithTags: Story = {
    args: {
        items: [
            {
                label: 'A page with many tags and a rather long title',
                iconClass: 'rz-icon-rz--status-published-fill',
                tags: [
                    'Culture',
                    'Théâtre',
                    'Saison 2026',
                    'Jeune public',
                    'Création',
                    'Festival',
                    'Partenariat',
                    'Archive',
                ],
            },
            {
                label: 'Two tags only',
                iconClass: 'rz-icon-rz--status-draft-fill',
                tags: ['Culture', 'Théâtre'],
            },
            {
                label: 'No tag',
                iconClass: 'rz-icon-rz--status-published-fill',
            },
        ],
    },
    decorators: [
        (story) => {
            const container = document.createElement('div')
            container.style.cssText =
                'resize: horizontal; overflow: hidden; width: 900px; max-width: 100%; padding: 8px; border: 1px dashed #ccc;'
            container.appendChild(story() as HTMLElement)

            return container
        },
    ],
    render: (args) => rootRenderer(args),
    play: async ({ canvasElement, args }) => {
        await nextFrame()
        await nextFrame()

        const rows = Array.from(
            canvasElement.querySelectorAll<HTMLElement>(
                `.${COMPONENT_CLASS_NAME}__item__node__inner`,
            ),
        )
        rows.forEach((row, i) => {
            const total = args.items[i].tags?.length ?? 0
            const tags = row.querySelector<HTMLElement>(
                `.${COMPONENT_CLASS_NAME}__item__tags`,
            )
            if (!tags) {
                if (total) throw new Error(`row ${i}: tags wrapper missing`)
                return
            }
            const inline = tags.querySelectorAll(':scope > rz-badge').length
            const counter = tags.querySelector('.rz-button__label')
            const folded = total - inline
            if (folded > 0 && counter?.textContent !== String(folded)) {
                throw new Error(
                    `row ${i}: counter "${counter?.textContent}" for ${folded} folded tags`,
                )
            }
            if (row.scrollWidth > row.clientWidth + 1) {
                throw new Error(`row ${i}: overflows`)
            }
            const label = row.querySelector<HTMLElement>(
                `.${COMPONENT_CLASS_NAME}__item__label`,
            )
            if (label && inline > 0 && label.offsetWidth < 120) {
                throw new Error(
                    `row ${i}: title squeezed to ${label.offsetWidth}px`,
                )
            }
        })
    },
}
