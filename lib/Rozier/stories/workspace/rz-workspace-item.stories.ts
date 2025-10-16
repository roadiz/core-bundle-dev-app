import type { Meta, StoryObj } from '@storybook/html-vite'
import '../../app/assets/css/rz-workspace/rz-workspace-item.css'

type ButtonArgs = {
    label: string
    selected: boolean
    iconClass: string
    variants: 'level-1' | 'level-2'
}

const meta: Meta<ButtonArgs> = {
    title: 'Components/Workspace/Item',
    // tags: ['autodocs'],
    args: {
        label: 'Workspace item label',
        selected: false,
        iconClass: 'rz-icon-ri--computer-line',
        variants: 'level-1',
    },
    argTypes: {
        variants: {
            options: ['level-1', 'level-2'],
            control: { type: 'radio' },
        },
    },
}

export default meta
type Story = StoryObj<ButtonArgs>

function iconRenderer(iconClass: string) {
    if (!iconClass) return ''
    return `<span aria-hidden="true" class="workspace-item__icon ${iconClass}"></span>`
}

export const Default: Story = {
    render: (args) => {
        const variantClass =
            args.variants === 'level-2'
                ? 'workspace-item-level-2'
                : 'workspace-item'
        const selectedClass = args.selected ? 'workspace-item--selected' : ''
        const classes = [variantClass, selectedClass].filter((c) => c).join(' ')

        return `<div class="${classes}">
			${iconRenderer(args.iconClass)}
			${args.label}
			</div>`
    },
}
