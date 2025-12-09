import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzDropdownRenderer,
    DEFAULT_ITEM,
} from '~/utils/storybook/renderer/rzDropDown'
import { type Args as PopoverItemArgs } from './RzDropdownItem.stories'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'

export type Args = {
    title?: string
    reverse?: boolean
    displayHeadElements?: boolean
    headElements?: Record<string, string>[]
    borderColor?: string
    footerContent?: string
    isOpen?: boolean
    items: (PopoverItemArgs | PopoverItemArgs[])[]
    listTag?: string
}

const meta: Meta<Args> = {
    title: 'Components/Overlay/Dropdown',
    tags: ['autodocs'],
    args: {
        title: 'Menu title',
        borderColor: '',
        isOpen: true,
        reverse: false,
        displayHeadElements: true,
        headElements: [
            {
                innerHTML: `
                <rz-tooltip tooltip-text="Status name">
                    <span class="rz-icon-rz--status-no-index-line"></span>
                </rz-tooltip>`,
            },
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

export const Default: Story = {
    render: (args) => {
        return rzDropdownRenderer(args)
    },
    args: {
        footerContent: 'Last edited by John D. Sep 10, 2025, 4:08 PM',
    },
}

export const Simple: Story = {
    render: (args) => {
        return rzDropdownRenderer(args)
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
        return rzDropdownRenderer(args)
    },
    args: {
        reverse: true,
        displayHeadElements: false,
    },
}

export const WithLinkItems: Story = {
    render: (args) => {
        return rzDropdownRenderer(args)
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

export const TreeWalkerDropdownMenu: Story = {
    render: (args) => {
        const { popover, target, popoverContent } = rzPopoverRenderer({
            placement: 'right-start',
            offset: 10,
            popoverElement: { id: 'TreeWalkerDropdownMenu' },
        })

        target.innerText = 'Toggle Dropdown Menu'

        rzDropdownRenderer(args, popoverContent)
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

export const QuickAccessNav: Story = {
    render: (args) => {
        const { popover, target, popoverContent } = rzPopoverRenderer({
            placement: 'bottom-start',
            offset: 10,
            popoverElement: { tag: 'nav', id: 'QuickAccessNav' },
        })

        target.classList.add(`rz-brand-watermark`)
        target.setAttribute('aria-label', 'Open quick access navigation')

        const icon = document.createElement('span')
        icon.classList.add('rz-icon-rz--logo-rz')
        target.appendChild(icon)

        rzDropdownRenderer(args, popoverContent)
        return popover
    },
    args: {
        title: undefined,
        displayHeadElements: false,
        isOpen: false,
        listTag: 'ul',
        items: [
            [
                {
                    tag: 'a',
                    label: 'Label',
                    iconClass: 'rz-icon-ri--user-6-line',
                    rightIconClass: 'rz-icon-ri--arrow-right-up-line',
                    attributes: { href: '#' },
                },
                {
                    tag: 'a',
                    label: 'Label 2',
                    iconClass: 'rz-icon-ri--user-6-line',
                    rightIconClass: 'rz-icon-ri--arrow-right-s-line',
                    attributes: { href: '#' },
                },
                {
                    tag: 'button',
                    label: 'Label 3',
                    iconClass: 'rz-icon-ri--user-6-line',
                    attributes: {
                        'aria-label': 'Label for this button action',
                    },
                },
                {
                    tag: 'a',
                    label: 'Label 4',
                    iconClass: 'rz-icon-ri--user-6-line',
                    rightIconClass: 'rz-icon-ri--arrow-right-s-line',
                    attributes: { href: '#' },
                },
            ],
        ],
    },
}
