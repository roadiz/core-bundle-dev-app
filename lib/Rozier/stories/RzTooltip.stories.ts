import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Placement } from '@floating-ui/dom'
import { POPOVER_PLACEMENTS, ATTRIBUTES_OPTIONS_MAP } from '~/utils/Popover'
import { rzButtonRenderer } from '~/utils/storybook/renderer/rzButton'

export type Args = {
    tooltipText?: string
    placement: Placement
    innerHtml: string
    offset: number
    shift: number
}

const COMPONENT_CLASS_NAME = 'rz-tooltip'

/**
 * To display simple tooltips, use the `tooltip-text` attribute on the element.
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

function setAttributes(element: HTMLElement, args: Args) {
    if (args.tooltipText) {
        element.setAttribute('tooltip-text', args.tooltipText)
    }
    if (args.placement) {
        element.setAttribute(ATTRIBUTES_OPTIONS_MAP.placement, args.placement)
    }
    if (args.offset) {
        element.setAttribute(
            ATTRIBUTES_OPTIONS_MAP.offset,
            args.offset.toString(),
        )
    }
    if (args.shift) {
        element.setAttribute(
            ATTRIBUTES_OPTIONS_MAP.shift,
            args.shift.toString(),
        )
    }
}

function rzTooltipRenderer(args: Args) {
    const tooltip = document.createElement(COMPONENT_CLASS_NAME)
    tooltip.innerHTML = args.innerHtml
    setAttributes(tooltip, args)

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
        innerHtml:
            '<span aria-label="Information" class="rz-icon-ri--information-line"></span>',
    },
}

export const Link: Story = {
    render: (args) => {
        return rzTooltipRenderer(args)
    },
    args: {
        innerHtml: '<a href="#">Hover me</a>',
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
			<button popovertarget="manual-popover-id">button label</button>
			<div popover="hint" id="manual-popover-id">
                <span class="rz-icon-ri--information-line"></span>
                <h3>Tooltip title</h3>
                <p>This is a custom tooltip content with an icon, a title and a paragraph.</p>
            </div>
		`,
    },
}

export const ButtonOnlyUsage: Story = {
    render: (args) => {
        const button = rzButtonRenderer({
            label: 'Hover me',
            attributes: {
                is: 'rz-button',
            },
        })

        setAttributes(button, args)
        return button
    },
    args: {
        tooltipText: 'This is a tooltip for a button element',
    },
}
