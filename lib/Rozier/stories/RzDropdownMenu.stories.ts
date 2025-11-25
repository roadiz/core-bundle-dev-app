import type { Meta, StoryObj } from '@storybook/html-vite'
import { BadgeArgs } from './RzBadge.stories'

const COMPOSANT_CLASS_NAME = 'rz-dropdown-menu'
const COMPOSANT_ITEM_CLASS_NAME = 'rz-dropdown-item'

type Item = {
    iconClass?: string
    label?: string
    description?: string
    badge?: BadgeArgs
    rightIconClass?: string
    tag?: string
}

export type Args = {
    title?: string
    reverse?: boolean
    headExtended?: boolean
    headElements?: Record<string, string>[]
    borderColor?: string
    items: (Item | Item[])[]
    footerContent?: string
}

const DEFAULT_ITEM = {
    iconClass: 'rz-icon-ri--user-6-line',
    label: 'Profile X',
    rightIconClass: 'rz-icon-ri--arrow-right-s-line',
}

const meta: Meta<Args> = {
    title: 'Components/DropdownMenu',
    tags: ['autodocs'],
    args: {
        title: 'Menu title',
        borderColor: '',
        reverse: false,
        headExtended: true,
        headElements: [
            {
                tag: 'span',
                class: 'rz-icon-ri--lock-line',
            },
            {
                tag: 'span',
                class: 'rz-icon-ri--eye-off-line',
            },
        ],
        items: [
            DEFAULT_ITEM,
            DEFAULT_ITEM,
            { tag: 'hr' },
            DEFAULT_ITEM,
            DEFAULT_ITEM,
        ],
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function rzDropdownItemRenderer(
    args: Item,
    itemClass: string = COMPOSANT_ITEM_CLASS_NAME,
) {
    const item = document.createElement(args.tag || 'div')
    item.classList.add(itemClass)

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.className = args.iconClass
        item.appendChild(icon)
    }

    if (args.label) {
        const label = document.createElement('span')
        label.classList.add(`${itemClass}__label`)
        label.innerHTML = args.label
        item.appendChild(label)
    }

    if (args.rightIconClass) {
        const icon = document.createElement('span')
        icon.className = args.rightIconClass
        item.appendChild(icon)
    }

    return item
}

function rzDropdownBodyRenderer(items: Item[]) {
    const body = document.createElement('menu')
    body.className = `${COMPOSANT_CLASS_NAME}__body`

    items.forEach((itemArgs) => {
        const itemWrapper = document.createElement('li')
        body.appendChild(itemWrapper)
        itemWrapper.classList.add(`${COMPOSANT_CLASS_NAME}__item`)
        if (itemArgs.tag === 'hr') {
            return
        }

        const item = rzDropdownItemRenderer(itemArgs)
        itemWrapper.appendChild(item)
    })

    return body
}

function rzDropdownMenuRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.className = COMPOSANT_CLASS_NAME
    if (args.reverse) {
        wrapper.classList.add(`${COMPOSANT_CLASS_NAME}--reverse`)
    }
    if (args.borderColor) {
        wrapper.style.setProperty(
            '--rz-dropdown-menu-border-color',
            args.borderColor,
        )
    }

    if (args.headElements?.length || args.title) {
        const head = document.createElement('div')
        head.className = `${COMPOSANT_CLASS_NAME}__head`

        if (args.title) {
            const title = document.createElement('div')
            title.className = `${COMPOSANT_CLASS_NAME}__head__title`
            title.innerText = args.title || ''
            head.appendChild(title)
        }

        if (args.headElements?.length) {
            const headElements = document.createElement('div')
            headElements.className = `${COMPOSANT_CLASS_NAME}__head__elements`
            args.headElements.forEach((el) => {
                const element = document.createElement(el.tag || 'div')
                Object.entries(el).forEach(([key, value]) => {
                    if (key !== 'tag') {
                        element.setAttribute(key, value)
                    }
                })
                headElements.appendChild(element)
            })
            if (args.headExtended) {
                head.appendChild(headElements)
            }
        }

        wrapper.appendChild(head)
    }

    const bodyList = Array.isArray(args.items[0]) ? args.items : [args.items]
    bodyList.forEach((bodyItems) => {
        console.log(bodyItems)
        const body = rzDropdownBodyRenderer(bodyItems)
        wrapper.appendChild(body)
    })
    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzDropdownMenuRenderer(args)
    },
}

export const WithMenuList: Story = {
    render: (args) => {
        return rzDropdownMenuRenderer(args)
    },
    args: {
        items: [
            [DEFAULT_ITEM, DEFAULT_ITEM],
            [DEFAULT_ITEM, DEFAULT_ITEM],
        ],
    },
}
