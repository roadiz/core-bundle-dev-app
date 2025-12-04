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
}

const COMPONENT_CLASS_NAME = 'rz-node-tree'
const meta: Meta<Args> = {
    title: 'Components/NodeTree',
    tags: ['autodocs'],
    args: {
        items: [
            {
                label: 'Menu 1',
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

function rzItemRenderer(item: Item) {
    const tag = item.children?.length ? 'button' : item.href ? 'a' : 'div'
    const itemEl = document.createElement(tag)
    itemEl.classList.add(`${COMPONENT_CLASS_NAME}__item`)

    const dragIcon = document.createElement('span')
    dragIcon.classList.add('rz-icon-ri--draggable')
    itemEl.appendChild(dragIcon)

    const typeIcon = document.createElement('span')
    typeIcon.classList.add(item.iconClass || 'rz-icon-ri--folder-line')
    itemEl.appendChild(typeIcon)

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
    label.textContent = item.label
    itemEl.appendChild(label)

    const moreButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--more-line',
        emphasis: 'tertiary',
        size: 'xs',
    })
    itemEl.appendChild(moreButton)

    const expandButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--arrow-down-s-line',
        emphasis: 'tertiary',
        size: 'xs',
    })
    itemEl.appendChild(expandButton)

    return itemEl
}

function rzLiRenderer(item: Item) {
    const li = document.createElement('li')
    li.classList.add(`${COMPONENT_CLASS_NAME}__li`)

    const itemEl = rzItemRenderer(item)
    li.appendChild(itemEl)

    if (item.children) {
        const childrenList = listRenderer(item.children)
        li.appendChild(childrenList)
    }

    return li
}

function listRenderer(items: Item[]) {
    const list = document.createElement('ul')
    list.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    items.forEach((item) => {
        const itemEl = rzLiRenderer(item)
        list.appendChild(itemEl)
    })

    return list
}

export const Default: Story = {
    render: (args) => {
        return listRenderer(args.items)
    },
}
