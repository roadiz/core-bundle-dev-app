import type { Meta, StoryObj } from '@storybook/html-vite'
import { BadgeArgs } from './RzBadge.stories'
import { rzBadgeRenderer } from '~/utils/storybook/renderer/rzBadge'

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
    displayHeadElements?: boolean
    headElements?: Record<string, string>[]
    borderColor?: string
    items: (Item | Item[])[]
    footerContent?: string
}

const DEFAULT_ITEM: Item = {
    iconClass: 'rz-icon-ri--user-6-line',
    label: 'Profile X',
    description: 'Profile description',
    rightIconClass: 'rz-icon-ri--arrow-right-s-line',
    badge: {
        label: 'D',
        size: 'xs',
        iconClass: 'rz-icon-ri--command-line',
        attributes: {
            'aria-label': 'command name shorthand',
        },
    },
}

const meta: Meta<Args> = {
    title: 'Components/DropdownMenu',
    tags: ['autodocs'],
    args: {
        title: 'Menu title',
        borderColor: '',
        reverse: false,
        displayHeadElements: true,
        headElements: [
            {
                tag: 'span',
                class: 'rz-icon-ri--lock-2-line',
            },
            {
                tag: 'span',
                class: 'rz-icon-ri--eye-off-line',
            },
            {
                innerHTML:
                    '<div class="rz-badge rz-badge--success"><span class="rz-badge__icon rz-icon-rz--status-published-fill"></span></div>',
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
        icon.classList.add(`${itemClass}__icon`, args.iconClass)
        item.appendChild(icon)
    }

    if (args.label || args.description) {
        const textWrapper = document.createElement('div')
        textWrapper.classList.add(`${itemClass}__text-wrapper`)
        item.appendChild(textWrapper)

        if (args.label) {
            const label = document.createElement('span')
            label.classList.add(`${itemClass}__label`)
            label.innerHTML = args.label
            textWrapper.appendChild(label)
        }

        if (args.description) {
            const description = document.createElement('div')
            description.classList.add(`${itemClass}__description`)
            description.innerHTML = args.description
            textWrapper.appendChild(description)
        }
    }

    if (args.badge) {
        const badge = rzBadgeRenderer(args.badge)
        badge.classList.add(`${itemClass}__badge`)
        item.appendChild(badge)
    }

    if (args.rightIconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${itemClass}__icon`, args.rightIconClass)
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

        if (args.displayHeadElements && args.headElements?.length) {
            const headElements = document.createElement('div')
            headElements.className = `${COMPOSANT_CLASS_NAME}__head__elements`
            head.appendChild(headElements)

            args.headElements.forEach((el) => {
                if (el.innerHTML) {
                    headElements.insertAdjacentHTML('beforeend', el.innerHTML)
                    return
                }

                const element = document.createElement(el.tag || 'div')
                Object.entries(el).forEach(([key, value]) => {
                    if (key !== 'tag') {
                        element.setAttribute(key, value)
                    }
                })
                headElements.appendChild(element)
            })
        }

        wrapper.appendChild(head)
    }

    const bodyList = Array.isArray(args.items[0]) ? args.items : [args.items]
    bodyList.forEach((bodyItems) => {
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

export const WithMultipleMenuTemplateGroup: Story = {
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

export const ReverseWithCollapsedHead: Story = {
    render: (args) => {
        return rzDropdownMenuRenderer(args)
    },
    args: {
        reverse: true,
        displayHeadElements: false,
    },
}

export const WithPopover: Story = {
    render: (args) => {
        const popover = document.createElement('rz-popover')
        popover.setAttribute('popover-placement', 'right-start')
        popover.setAttribute('popover-offset', '10px')

        const contentId = 'dropdown-menu'
        const target = document.createElement('button')
        target.innerText = 'Toggle Dropdown Menu'
        target.setAttribute('popovertarget', contentId)
        popover.appendChild(target)

        const content = rzDropdownMenuRenderer(args)
        content.id = contentId
        content.setAttribute('popover', '')

        popover.appendChild(content)

        return popover
    },
    args: {
        borderColor: 'blue',
        items: [
            {
                iconClass: 'rz-icon-ri--add-line',
                label: 'Add a child',
                badge: {
                    label: 'D',
                    size: 'xs',
                    iconClass: 'rz-icon-ri--command-line',
                    attributes: {
                        'aria-label': 'command name shorthand',
                    },
                },
            },
            {
                iconClass: 'rz-icon-ri--arrow-up-line',
                label: 'Move first',
            },
            {
                iconClass: 'rz-icon-ri--arrow-down-line',
                label: 'Move last',
            },
            {
                iconClass: 'rz-icon-ri--edit-line',
                label: 'Edit',
            },
            {
                tag: 'hr',
            },
            {
                iconClass: 'rz-icon-ri--eye-line',
                label: 'Make visible',
            },
            {
                iconClass: 'rz-icon-rz--status-draft-line',
                label: 'Unpublish',
            },
            {
                iconClass: 'rz-icon-ri--file-copy-2-line',
                label: 'Copy',
            },
            {
                iconClass: 'rz-icon-ri--clipboard-line',
                label: 'Paste after',
            },
            {
                iconClass: 'rz-icon-ri--clipboard-line',
                label: 'Paste inside',
            },
            {
                iconClass: 'rz-icon-ri--file-copy-line',
                label: 'Duplicate',
            },
        ],
    },
}
