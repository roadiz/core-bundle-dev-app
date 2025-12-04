import type { Meta, StoryObj } from '@storybook/html-vite'
import { BadgeArgs } from './RzBadge.stories'
import { rzBadgeRenderer } from '~/utils/storybook/renderer/rzBadge'

const COMPONENT_CLASS_NAME = 'rz-dropdown-menu'

type Item = {
    iconClass?: string
    label?: string
    description?: string
    badge?: BadgeArgs
    rightIconClass?: string
    tag?: string
    attributes?: Record<string, string>
}

export type Args = {
    title?: string
    reverse?: boolean
    displayHeadElements?: boolean
    headElements?: Record<string, string>[]
    borderColor?: string
    items: (Item | Item[])[]
    footerContent?: string
    isOpen?: boolean
}

const DEFAULT_ITEM: Item = {
    tag: 'button',
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
        isOpen: true,
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
            [DEFAULT_ITEM, DEFAULT_ITEM],
            [DEFAULT_ITEM, DEFAULT_ITEM],
        ],
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function rzDropdownItemRenderer(args: Item, itemClass: string) {
    const item = document.createElement(args.tag || 'div')
    item.classList.add(itemClass)

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (key !== 'tag') {
            item.setAttribute(key, value)
        }
    })

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
    body.className = `${COMPONENT_CLASS_NAME}__list`

    items.forEach((itemArgs) => {
        const itemWrapper = document.createElement('li')
        body.appendChild(itemWrapper)
        itemWrapper.classList.add(`${COMPONENT_CLASS_NAME}__item`)
        if (itemArgs.tag === 'hr') {
            return
        }

        const item = rzDropdownItemRenderer(
            itemArgs,
            `${COMPONENT_CLASS_NAME}__button`,
        )
        itemWrapper.appendChild(item)
    })

    return body
}

function rzDropdownMenuRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.className = COMPONENT_CLASS_NAME
    if (args.isOpen) {
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--open`)
    }
    if (args.reverse) {
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--reverse`)
    }
    if (args.borderColor) {
        wrapper.style.setProperty(
            '--rz-dropdown-menu-border-color',
            args.borderColor,
        )
    }

    if (args.headElements?.length || args.title) {
        const head = document.createElement('div')
        head.className = `${COMPONENT_CLASS_NAME}__head`

        if (args.title) {
            const title = document.createElement('div')
            title.className = `${COMPONENT_CLASS_NAME}__title`
            title.innerText = args.title || ''
            head.appendChild(title)
        }

        if (args.displayHeadElements && args.headElements?.length) {
            const headElements = document.createElement('ul')
            headElements.className = `${COMPONENT_CLASS_NAME}__info-list`
            head.appendChild(headElements)

            args.headElements.forEach((el) => {
                const infoItem = document.createElement('li')
                infoItem.classList.add(`${COMPONENT_CLASS_NAME}__info-item`)
                headElements.appendChild(infoItem)

                if (el.innerHTML) {
                    infoItem.innerHTML += el.innerHTML
                } else {
                    const element = document.createElement(el.tag || 'div')
                    Object.entries(el).forEach(([key, value]) => {
                        if (key !== 'tag') {
                            element.setAttribute(key, value)
                        }
                    })
                    infoItem.appendChild(element)
                }
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

export const ReverseWithCollapsedHead: Story = {
    render: (args) => {
        return rzDropdownMenuRenderer(args)
    },
    args: {
        reverse: true,
        displayHeadElements: false,
    },
}

export const WithLinkItems: Story = {
    render: (args) => {
        return rzDropdownMenuRenderer(args)
    },
    args: {
        reverse: true,
        displayHeadElements: false,
        items: [
            {
                tag: 'a',
                attributes: {
                    href: '#',
                },
                iconClass: 'rz-icon-ri--file-copy-line',
                label: 'Duplicate',
                rightIconClass: 'rz-icon-ri--arrow-right-s-line',
            },
            {
                tag: 'a',
                attributes: {
                    href: '#',
                },
                iconClass: 'rz-icon-ri--delete-bin-6-line',
                label: 'Delete',
            },
        ],
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
        isOpen: false,
        borderColor: 'blue',
        items: [
            [
                {
                    iconClass: 'rz-icon-ri--add-line',
                    label: 'Add a child',
                    tag: 'button',
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
                    tag: 'button',
                },
                {
                    iconClass: 'rz-icon-ri--arrow-down-line',
                    label: 'Move last',
                    tag: 'button',
                },
                {
                    iconClass: 'rz-icon-ri--edit-line',
                    label: 'Edit',
                    tag: 'button',
                },
            ],
            [
                {
                    iconClass: 'rz-icon-ri--eye-line',
                    label: 'Make visible',
                    tag: 'button',
                },
                {
                    iconClass: 'rz-icon-rz--status-draft-line',
                    label: 'Unpublish',
                    tag: 'button',
                },
                {
                    iconClass: 'rz-icon-ri--file-copy-2-line',
                    label: 'Copy',
                    tag: 'button',
                },
                {
                    iconClass: 'rz-icon-ri--clipboard-line',
                    label: 'Paste after',
                    tag: 'button',
                },
                {
                    iconClass: 'rz-icon-ri--clipboard-line',
                    label: 'Paste inside',
                    tag: 'button',
                },
                {
                    iconClass: 'rz-icon-ri--file-copy-line',
                    label: 'Duplicate',
                    tag: 'button',
                },
            ],
        ],
    },
}
