import { rzBadgeRenderer } from '~/utils/component-renderer/rzBadge'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'

export type RzTreeItemOptions = {
    label: string
    iconClass?: string
    icon?: HTMLElement
    href?: string
    expanded?: boolean
    children?: RzTreeItemOptions[]
    actions?: HTMLElement[]
    /** Stack tree only: tags fold into a popover past MAX_VISIBLE_TAGS or when the row is narrow. */
    tags?: string[]
}

/** Mirrors `visibleCount` in widgets/nodeTree/singleNode.html.twig — keep both in step. */
const MAX_VISIBLE_TAGS = 6

export type RzTreeOptions = {
    items: RzTreeItemOptions[]
    iconColor?: string
    /** Adds rz-tree--child-nodes, the layout of the children nodes tree. */
    childNodes?: boolean
}

export const COMPONENT_CLASS_NAME = 'rz-tree'

function itemNodeRenderer(item: RzTreeItemOptions) {
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

function itemRenderer(item: RzTreeItemOptions) {
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

function listRenderer(items: RzTreeItemOptions[]) {
    const element = document.createElement('ul')

    element.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    items.forEach((item) => {
        const itemEl = itemRenderer(item)
        element.appendChild(itemEl)
    })

    return element
}

export function rzTreeRenderer(args: RzTreeOptions) {
    const element = document.createElement('rz-tree')
    element.classList.add(COMPONENT_CLASS_NAME)

    const list = listRenderer(args.items)
    list.setAttribute('role', 'tree')
    element.appendChild(list)

    if (args.iconColor) {
        element.style.setProperty('--rz-tree-icon-color', args.iconColor)
    }

    if (args.childNodes) {
        element.classList.add(`${COMPONENT_CLASS_NAME}--child-nodes`)
    }

    return element
}
