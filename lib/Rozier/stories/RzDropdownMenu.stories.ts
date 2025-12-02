import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzPopoverItemRenderer,
    DEFAULT_ITEM,
} from '~/utils/storybook/renderer/rzPopoverItem'
import { type Args as PopoverItemArgs } from './RzPopoverItem.stories'

const COMPONENT_CLASS_NAME = 'rz-dropdown-menu'

export type Args = {
    title?: string
    reverse?: boolean
    displayHeadElements?: boolean
    headElements?: Record<string, string>[]
    borderColor?: string
    footerContent?: string
    isOpen?: boolean
    items: (PopoverItemArgs | PopoverItemArgs[])[]
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

function rzDropdownBodyRenderer(items: PopoverItemArgs[]) {
    const body = document.createElement('menu')
    body.className = `${COMPONENT_CLASS_NAME}__list`

    items.forEach((itemArgs) => {
        const itemWrapper = document.createElement('li')
        body.appendChild(itemWrapper)

        if (itemArgs.tag === 'hr') {
            return
        }

        const item = rzPopoverItemRenderer(itemArgs)
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

    const headElements = args.displayHeadElements ? args.headElements : []
    if (args.title || headElements.length) {
        const head = document.createElement('div')
        head.className = `${COMPONENT_CLASS_NAME}__head`

        if (args.title) {
            const title = document.createElement('div')
            title.className = `${COMPONENT_CLASS_NAME}__title`
            title.innerText = args.title || ''
            head.appendChild(title)
        }

        if (headElements.length) {
            const headElementList = document.createElement('ul')
            headElementList.className = `${COMPONENT_CLASS_NAME}__info-list`
            head.appendChild(headElementList)

            headElements.forEach((el) => {
                const infoItem = document.createElement('li')
                infoItem.classList.add(`${COMPONENT_CLASS_NAME}__info-item`)
                headElementList.appendChild(infoItem)

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

export const Simple: Story = {
    render: (args) => {
        return rzDropdownMenuRenderer(args)
    },
    args: {
        title: '',
        displayHeadElements: false,
        items: [
            [DEFAULT_ITEM, DEFAULT_ITEM, DEFAULT_ITEM, DEFAULT_ITEM].map(
                (i) => ({ ...i, description: undefined, badge: undefined }),
            ),
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
