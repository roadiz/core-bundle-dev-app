import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/storybook/renderer/rzButton'

type Item = {
    label: string
    iconClass?: string
    href?: string
    children?: Item[]
}

export type Args = {
    items: Item[]
    iconColor?: string
}

const COMPONENT_CLASS_NAME = 'rz-node-tree'
const meta: Meta<Args> = {
    title: 'Components/NodeTree',
    tags: ['autodocs'],
    args: {
        iconColor: '',
        items: [
            {
                label: 'Menu 1',
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
                    { label: 'item 1.4' },
                ],
            },
            { label: 'Page 1' },
            { label: 'Page 2' },
            {
                label: 'Menu 2',
                children: [{ label: 'item 2.1' }, { label: 'item 2.2' }],
            },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function rzNodeTreeItemRenderer(item: Item) {
    const hasChildren = item.children && item.children.length > 0
    const tag = item.href ? 'a' : 'div'
    const itemEl = document.createElement(tag)
    itemEl.classList.add(`${COMPONENT_CLASS_NAME}__item`)

    const dragIcon = document.createElement('span')
    dragIcon.classList.add('rz-icon-ri--draggable')
    itemEl.appendChild(dragIcon)

    const typeIcon = document.createElement('span')
    typeIcon.classList.add(`${COMPONENT_CLASS_NAME}__icon`)
    typeIcon.classList.add(item.iconClass || 'rz-icon-ri--folder-fill')
    itemEl.appendChild(typeIcon)

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
    label.textContent = item.label
    itemEl.appendChild(label)

    if (hasChildren) {
        const expandButton = rzButtonRenderer({
            tag: 'button',
            iconClass: 'rz-icon-ri--arrow-down-s-line',
            emphasis: 'tertiary',
            size: 'xs',
            attributes: {
                'aria-expanded': 'true',
                'aria-label': 'Expand/Collapse node children',
            },
        })
        expandButton.classList.add(`${COMPONENT_CLASS_NAME}__expand-button`)

        itemEl.appendChild(expandButton)
    }

    const moreButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--more-line',
        emphasis: 'tertiary',
        size: 'xs',
    })
    itemEl.appendChild(moreButton)

    return itemEl
}

function rzNodeTreeListItemRenderer(item: Item) {
    const ITEM_LIST_CLASS = 'rz-node-tree-list-item'

    const li = document.createElement('li', { is: ITEM_LIST_CLASS })
    li.setAttribute('is', ITEM_LIST_CLASS)
    li.classList.add(`${COMPONENT_CLASS_NAME}__list-item`)

    const itemEl = rzNodeTreeItemRenderer(item)
    li.appendChild(itemEl)

    if (item.children) {
        const childrenList = listRenderer(item.children)
        childrenList.classList.add(`${COMPONENT_CLASS_NAME}__list--sub`)
        li.appendChild(childrenList)
    }
    return li
}

function listRenderer(items: Item[]) {
    const list = document.createElement('ul')
    list.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    items.forEach((item) => {
        const itemEl = rzNodeTreeListItemRenderer(item)
        list.appendChild(itemEl)
    })

    return list
}

function nodeTreeRenderer(args: Args) {
    const tree = listRenderer(args.items)
    tree.classList.add(`${COMPONENT_CLASS_NAME}__list--root`)

    if (args.iconColor) {
        tree.style.setProperty('--rz-node-tree-icon-color', args.iconColor)
    }
    return tree
}

export const Default: Story = {
    render: (args) => {
        return nodeTreeRenderer(args)
    },
}
