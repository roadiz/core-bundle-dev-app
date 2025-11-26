import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Placement } from '@floating-ui/dom'

export type Args = {
    targetElement: {
        tag?: string
        text: string
        attributes: Record<string, string>
    }
    popoverContent: {
        id: string
        content: string
    }
    popoverPlacement?: Placement
    popoverOffset?: number
    popoverShift?: number
}

const POPOVER_PLACEMENT = [
    'top',
    'right',
    'bottom',
    'left',
    'top-start',
    'top-end',
    'right-start',
    'right-end',
    'bottom-start',
    'bottom-end',
    'left-start',
    'left-end',
]

const meta: Meta<Args> = {
    title: 'Components/Popover',
    tags: ['autodocs'],
    args: {
        targetElement: {
            text: 'Click me',
            attributes: {
                class: 'rz-button',
            },
        },
        popoverContent: {
            id: 'popover-content-1',
            content: '<p>This is the popover content.</p>',
        },
        popoverPlacement: 'bottom',
        popoverOffset: 20,
        popoverShift: 20,
    },
    argTypes: {
        popoverPlacement: {
            control: { type: 'select' },
            options: POPOVER_PLACEMENT,
        },
    },
}

export default meta
type Story = StoryObj<Args>

function rzPopoverRenderer(args: Args) {
    const popover = document.createElement('rz-popover')
    if (args.popoverPlacement) {
        popover.setAttribute('popover-placement', args.popoverPlacement)
    }
    if (args.popoverOffset) {
        popover.setAttribute('popover-offset', args.popoverOffset.toString())
    }
    if (args.popoverShift) {
        popover.setAttribute('popover-shift', args.popoverShift.toString())
    }

    const target = document.createElement(args.targetElement.tag || 'button')
    target.textContent = args.targetElement.text
    for (const [key, value] of Object.entries(args.targetElement.attributes)) {
        target.setAttribute(key, value)
    }
    target.setAttribute('popovertarget', args.popoverContent.id)
    popover.appendChild(target)

    const popoverContent = document.createElement('div')
    popoverContent.setAttribute('popover', '')
    popoverContent.id = args.popoverContent.id
    popoverContent.innerHTML = args.popoverContent.content
    popover.appendChild(popoverContent)

    return popover
}

export const Default: Story = {
    render: (args) => {
        const el = rzPopoverRenderer(args)
        el.style = `
		    display: block;
    		margin-inline: auto;
    		width: fit-content;
		`
        return el
    },
}

export const LeftPositionWithFlip: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')

        const item = rzPopoverRenderer(args)
        wrapper.appendChild(item)

        return wrapper
    },
    args: {
        popoverContent: {
            id: 'popover-content-2',
            content: '<p>This is a popover inside a scrollable page.</p>',
        },
        popoverPlacement: 'left',
    },
}

export const WithScroll: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.style.height = '200vh'
        wrapper.style.width = 'fit-content'
        wrapper.style.marginInline = 'auto'

        const item = rzPopoverRenderer(args)
        wrapper.appendChild(item)

        return wrapper
    },
    args: {
        popoverContent: {
            id: 'popover-content-3',
            content: '<p>This is a popover inside a scrollable page.</p>',
        },
        popoverPlacement: 'top',
    },
}
