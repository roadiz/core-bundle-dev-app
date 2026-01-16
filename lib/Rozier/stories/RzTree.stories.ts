import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

type Item = {
    label: string
    iconClass?: string
    href?: string
    expanded?: boolean
    children?: Item[]
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

    const moreButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--more-line',
        emphasis: 'tertiary',
        size: 'xs',
    })
    innerEl.appendChild(moreButton)

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
