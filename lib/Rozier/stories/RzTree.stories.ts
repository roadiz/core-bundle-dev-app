import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

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

function rzTreeItemRenderer(item: Item) {
    const hasChildren = item.children && item.children.length > 0
    const tag = item.href ? 'a' : 'div'
    const itemEl = document.createElement(tag)
    itemEl.classList.add(`${COMPONENT_CLASS_NAME}__item`)

    const innerEl = document.createElement('div')
    innerEl.classList.add(`${COMPONENT_CLASS_NAME}__item__inner`)
    itemEl.appendChild(innerEl)

    const dragIcon = document.createElement('span')
    dragIcon.classList.add('rz-icon-ri--draggable')
    innerEl.appendChild(dragIcon)

    const typeIcon = document.createElement('span')
    typeIcon.classList.add(`${COMPONENT_CLASS_NAME}__icon`)
    typeIcon.classList.add(item.iconClass || 'rz-icon-ri--folder-fill')
    innerEl.appendChild(typeIcon)

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
    label.textContent = item.label
    innerEl.appendChild(label)

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

        innerEl.appendChild(expandButton)
    }

    const moreButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--more-line',
        emphasis: 'tertiary',
        size: 'xs',
    })
    innerEl.appendChild(moreButton)

    return itemEl
}

function rzTreeListItemRenderer(item: Item) {
    const ITEM_LIST_CLASS = 'rz-tree-list-item'

    const li = document.createElement('li', { is: ITEM_LIST_CLASS })
    li.setAttribute('is', ITEM_LIST_CLASS)
    li.classList.add(`${COMPONENT_CLASS_NAME}__list-item`)

    const itemEl = rzTreeItemRenderer(item)
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
        const itemEl = rzTreeListItemRenderer(item)
        list.appendChild(itemEl)
    })

    return list
}

function treeRenderer(args: Args) {
    const root = document.createElement('rz-tree')
    root.classList.add(COMPONENT_CLASS_NAME)

    const tree = listRenderer(args.items)
    root.appendChild(tree)

    if (args.iconColor) {
        root.style.setProperty('--rz-tree-icon-color', args.iconColor)
    }

    return root
}

export const Default: Story = {
    args: {
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
        return treeRenderer(args)
    },
}

export const Large: Story = {
    args: {
        items: [
            {
                label: 'Menu 1',
                iconClass: 'rz-icon-ri--home-2-fill',
                children: [
                    { label: 'item 1.1' },
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
            { label: 'Page 2' },
            {
                label: 'Menu 2',
                children: [{ label: 'item 2.1' }, { label: 'item 2.2' }],
            },
        ],
    },
    render: (args) => {
        const tree = treeRenderer(args)
        tree.classList.add(`${COMPONENT_CLASS_NAME}--large`)
        return tree
    },
}
