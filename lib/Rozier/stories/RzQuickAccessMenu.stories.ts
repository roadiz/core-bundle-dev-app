import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzPopoverRenderer,
    Args as RzPopoverArgs,
} from '~/utils/storybook/renderer/rzPopover'
import { rzPopoverItemRenderer } from '~/utils/storybook/renderer/rzPopoverItem'
import type { Args as rzPopoverItemArgs } from './RzPopoverItem.stories'

export type Args = RzPopoverArgs & {
    items: rzPopoverItemArgs[]
}

const COMPONENT_CLASS_NAME = 'rz-quick-access-menu'

const meta: Meta<Args> = {
    title: 'Components/Quick Access Menu',
    tags: ['autodocs'],
    args: {
        popoverElement: {
            tag: 'ul',
            id: 'quick-access-menu',
        },
        placement: 'bottom-start',
        offset: 8,
        items: [
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
                attributes: { 'aria-label': 'Label for this button action' },
            },
            {
                tag: 'a',
                label: 'Label 4',
                iconClass: 'rz-icon-ri--user-6-line',
                rightIconClass: 'rz-icon-ri--arrow-right-s-line',
                attributes: { href: '#' },
            },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function rzQuickAccessMenuRenderer(args: Args) {
    const { popover, target, popoverContent } = rzPopoverRenderer(args)

    target.classList.add(`${COMPONENT_CLASS_NAME}__target`)
    const icon = document.createElement('span')
    icon.classList.add('rz-icon-rz--logo-rz')
    target.appendChild(icon)

    popoverContent.classList.add(`${COMPONENT_CLASS_NAME}__popover`)

    args.items.forEach((itemArgs) => {
        const listItem = document.createElement('li')
        const item = rzPopoverItemRenderer(itemArgs)
        listItem.appendChild(item)

        popoverContent.appendChild(listItem)
    })

    return popover
}

export const Default: Story = {
    render: (args) => {
        return rzQuickAccessMenuRenderer(args)
    },
}
