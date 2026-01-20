import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzDropdownRenderer } from '~/utils/storybook/renderer/rzDropDown'

type Item = {
    label: string
    iconClass?: string
    href?: string
    expanded?: boolean
    children?: Item[]
    actions?: HTMLElement[]
}

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

    const icon = document.createElement('span')
    icon.classList.add(`${COMPONENT_CLASS_NAME}__item__icon`)
    icon.classList.add(item.iconClass || 'rz-icon-ri--folder-fill')
    innerEl.appendChild(icon)

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__item__label`)
    label.textContent = item.label
    innerEl.appendChild(label)

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
                    { label: 'item 1.1' },
                    { label: 'item 1.2' },
                    {
                        label: 'item 1.3',
                        expanded: true,
                        children: [
                            { label: 'item 1.3.1' },
                            {
                                label: 'item 1.3.2',
                                children: [{ label: 'item 1.3.2.1' }],
                            },
                        ],
                    },
                    { label: 'item 1.4' },
                ],
            },
            { label: 'Page 1' },
            { label: 'Page 2' },
            {
                label: 'Menu 2',
                children: [{ label: 'item 2.1' }, { label: 'item 2.2' }],
            },
            {
                label: 'Menu 3',
                iconClass: 'rz-icon-ri--home-2-fill',
                children: [
                    { label: 'item 1.1' },
                    { label: 'item 1.2' },
                    {
                        label: 'item 1.3',
                        children: [
                            { label: 'item 1.3.1' },
                            {
                                label: 'item 1.3.2',
                                children: [{ label: 'item 1.3.2.1' }],
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
                iconClass: 'rz-icon-ri--home-2-fill',
                expanded: true,
                children: [
                    { label: 'item 1.1' },
                    {
                        label: 'item 1.3',
                        expanded: true,
                        children: [
                            { label: 'item 1.3.1' },
                            {
                                label: 'item 1.3.2',
                                children: [{ label: 'item 1.3.2.1' }],
                            },
                        ],
                    },
                    { label: 'item 1.4' },
                ],
            },
            { label: 'Page 2' },
            {
                label: 'Menu 2',
                expanded: true,
                children: [{ label: 'item 2.1' }, { label: 'item 2.2' }],
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
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Contact',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Services',
                        expanded: true,
                        actions: [createContextualMenuAction()],
                        children: [
                            {
                                label: 'Web development',
                                actions: [createContextualMenuAction()],
                            },
                            {
                                label: 'Mobile apps',
                                actions: [createContextualMenuAction()],
                            },
                        ],
                    },
                ],
            },
            {
                label: 'Blog',
                actions: [createContextualMenuAction()],
            },
            {
                label: 'Products',
                actions: [createContextualMenuAction()],
                children: [
                    {
                        label: 'Product A',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Product B',
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
