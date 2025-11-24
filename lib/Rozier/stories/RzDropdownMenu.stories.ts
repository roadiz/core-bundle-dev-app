import type { Meta, StoryObj } from '@storybook/html-vite'
import { BadgeArgs } from './RzBadge.stories'

const COMPOSANT_CLASS_NAME = 'rz-dropdown-menu'

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
    columnReverse?: boolean
    headElements?: Record<string, string>[]
    color?: string
    items: Item[]
    footerContent?: string
}

const meta: Meta<Args> = {
    title: 'Components/DropdownMenu',
    tags: ['autodocs'],
    args: {
        title: 'this is a title',
        color: 'red',
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
            {
                iconClass: 'rz-icon-ri--user-6-line',
                label: 'Profile',
                rightIconClass: 'rz-icon-ri--arrow-right-s-line',
            },
            {
                iconClass: 'rz-icon-ri--user-6-line',
                label: 'Profile 2',
                rightIconClass: 'rz-icon-ri--arrow-right-s-line',
            },
            {
                tag: 'hr',
            },
        ],
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function rzDropdownItemRenderer(args: Item) {
    const item = document.createElement(args.tag || 'div')
    item.className = `${COMPOSANT_CLASS_NAME}__item`

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.className = args.iconClass
        item.appendChild(icon)
    }

    if (args.label) {
        const label = document.createElement('span')
        label.className = `${COMPOSANT_CLASS_NAME}__item__label`
        label.innerHTML = args.label
        item.appendChild(label)
    }

    return item
}

function rzDropdownMenuRenderer(args: Args) {
    const wrapper = document.createElement('menu')
    wrapper.className = COMPOSANT_CLASS_NAME

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
            head.appendChild(headElements)
        }

        wrapper.appendChild(head)
    }

    const body = document.createElement('div')
    body.className = `${COMPOSANT_CLASS_NAME}__body`
    wrapper.appendChild(body)

    args.items?.forEach((itemArgs) => {
        const item = rzDropdownItemRenderer(itemArgs)
        body.appendChild(item)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzDropdownMenuRenderer(args)
    },
}
