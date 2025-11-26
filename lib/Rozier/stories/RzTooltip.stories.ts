import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Placement } from '@floating-ui/dom'
import { POPOVER_PLACEMENTS } from '~/utils/popover'

export type Args = {
    tooltipText?: string
    placement: Placement
    trigger: 'hover' | 'click' | 'focus'
    delayShow?: number
    delayHide?: number
    innerHtml: string
    offset: number
    shift: number
}

const COMPONENT_CLASS_NAME = 'rz-tooltip'

const meta: Meta<Args> = {
    title: 'Components/Tooltip',
    tags: ['autodocs'],
    args: {
        tooltipText: 'Tooltip example',
        innerHtml: 'base element for tooltip',
        placement: 'top',
        offset: 12,
        shift: 0,
    },
    argTypes: {
        placement: {
            control: { type: 'select' },
            options: POPOVER_PLACEMENTS,
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function rzTooltipRenderer(args: Args) {
    const tooltip = document.createElement(COMPONENT_CLASS_NAME)
    tooltip.innerHTML = args.innerHtml

    if (args.tooltipText) {
        tooltip.setAttribute('data-popover-text', args.tooltipText)
    }
    if (args.placement) {
        tooltip.setAttribute('data-popover-placement', args.placement)
    }
    if (args.trigger) {
        tooltip.setAttribute('data-popover-trigger', args.trigger)
    }
    if (args.offset) {
        tooltip.setAttribute('data-popover-offset', args.offset.toString())
    }
    if (args.shift) {
        tooltip.setAttribute('data-popover-shift', args.shift.toString())
    }
    if (args.delayShow) {
        tooltip.setAttribute(
            'data-popover-delay-show',
            args.delayShow.toString(),
        )
    }
    if (args.delayHide) {
        tooltip.setAttribute(
            'data-popover-delay-hide',
            args.delayHide.toString(),
        )
    }

    return tooltip
}

export const Default: Story = {
    render: (args) => {
        return rzTooltipRenderer(args)
    },
}

export const WithIcon: Story = {
    render: (args) => {
        return rzTooltipRenderer(args)
    },
    args: {
        innerHtml: '<span class="rz-icon-ri--information-line"></span>',
    },
}

export const WithTooltipChildren: Story = {
    render: (args) => {
        return rzTooltipRenderer(args)
    },
    args: {
        tooltipText: undefined,
        innerHtml: `
			<button popovertarget="popover-id" class="rz-button">
				<span class="rz-button__label">button label</span>
				<span class="rz-button__icon rz-icon-ri--arrow-drop-right-line"></span>
			</button>
			<div popover id="popover-id">Tooltip content</div>
		`,
    },
}
