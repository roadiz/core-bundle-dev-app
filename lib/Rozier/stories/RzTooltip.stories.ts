import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Placement } from '@floating-ui/dom'
import { POPOVER_PLACEMENTS, ATTRIBUTES_OPTIONS_MAP } from '~/utils/Popover'

export type Args = {
    tooltipText?: string
    placement: Placement
    innerHtml: string
    offset: number
    shift: number
}

const COMPONENT_CLASS_NAME = 'rz-tooltip'

/**
 * To display simple tooltips, use the `data-popover-text` attribute on the element.
 * An tooltip will be automatically created with the provided text content.
 *
 * For more complex tooltips, you can define a custom tooltip content
 * by adding a child element with the `popover="hint"` attribute and
 * a unique ID, then reference that ID in a `popovertarget` attribute
 * on the target element.
 */
const meta: Meta<Args> = {
    title: 'Components/Tooltip',
    tags: ['autodocs'],
    args: {
        tooltipText:
            'Tooltip text auto injected with data-popover-text from rz-tooltip',
        innerHtml: 'base element for tooltip',
        offset: 0,
        shift: 0,
    },
    argTypes: {
        placement: {
            control: { type: 'select' },
            options: POPOVER_PLACEMENTS,
            table: {
                defaultValue: { summary: 'top' },
            },
        },
        offset: {
            table: {
                defaultValue: { summary: '4px' },
            },
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
        tooltip.setAttribute(ATTRIBUTES_OPTIONS_MAP.placement, args.placement)
    }
    if (args.offset) {
        tooltip.setAttribute(
            ATTRIBUTES_OPTIONS_MAP.offset,
            args.offset.toString(),
        )
    }
    if (args.shift) {
        tooltip.setAttribute(
            ATTRIBUTES_OPTIONS_MAP.shift,
            args.shift.toString(),
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

export const WithCustomTooltip: Story = {
    render: (args) => {
        return rzTooltipRenderer(args)
    },
    args: {
        offset: 20,
        tooltipText: undefined,
        innerHtml: `
			<button popovertarget="popover-id" class="rz-button">
				<span class="rz-button__label">button label</span>
				<span class="rz-button__icon rz-icon-ri--arrow-drop-right-line"></span>
			</button>
			<div popover="hint" id="popover-id">
                <span class="rz-icon-ri--information-line"></span>
                <h3>Tooltip title</h3>
                <p>This is a custom tooltip content with an icon, a title and a paragraph.</p>
            </div>
		`,
    },
}
