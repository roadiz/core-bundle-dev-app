import type { Meta, StoryObj } from '@storybook/html-vite'

type PopoverItem = {
    title: string
    iconClassLeft?: string
    iconClassRight?: string
}

export type Args = {
    title?: string
    items: PopoverItem[]
}

const meta: Meta<Args> = {
    title: 'Components/Popover/Content',
    tags: ['autodocs'],
    args: {
        title: 'Popover Title',
        items: [
            {
                title: 'First Item',
                iconClassLeft: 'rz-icon-ri--star-line',
            },
            {
                title: 'Second Item',
                iconClassLeft: 'rz-icon-ri--heart-line',
                iconClassRight: 'rz-icon-ri--arrow-right-s-line',
            },
            {
                title: 'Third Item',
            },
        ],
    },
    argTypes: {},
}

export default meta
type Story = StoryObj<Args>

function rzPopoverItemRenderer(itemArgs: PopoverItem) {
    const wrapper = document.createElement('div')
    wrapper.classList.add('rz-popover__item')

    if (itemArgs.iconClassLeft) {
        const iconLeft = document.createElement('span')
        iconLeft.classList.add(
            'rz-popover__item__icon-left',
            itemArgs.iconClassLeft,
        )
        wrapper.appendChild(iconLeft)
    }

    const title = document.createElement('span')
    title.classList.add('rz-popover__item__title')
    title.textContent = itemArgs.title
    wrapper.appendChild(title)

    if (itemArgs.iconClassRight) {
        const iconRight = document.createElement('span')
        iconRight.classList.add(
            'rz-popover__item__icon-right',
            itemArgs.iconClassRight,
        )
        wrapper.appendChild(iconRight)
    }

    return wrapper
}

function rzPopoverContentRenderer(args: Args) {
    const popover = document.createElement('div')
    popover.classList.add('rz-popover')

    const popoverContent = document.createElement('div')
    popoverContent.classList.add('rz-popover__content')

    args.items.forEach((itemArgs) => {
        const item = rzPopoverItemRenderer(itemArgs)
        popoverContent.appendChild(item)
    })

    popover.appendChild(popoverContent)
    return popover
}

export const Default: Story = {
    render: (args) => {
        return rzPopoverContentRenderer(args)
    },
}
