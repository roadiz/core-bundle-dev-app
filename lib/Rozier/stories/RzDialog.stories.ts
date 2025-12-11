import type { Meta, StoryObj } from '@storybook/html-vite'
import type { ButtonArgs } from './RzButton.stories'
import { rzDialogWrapperRenderer } from '~/utils/storybook/renderer/rzDialog'

// 'none' = dialog cannot be closed by user interaction
// 'closerequest' = dialog can be closed only by close button or calling close() method
// 'any' = dialog can be closed by clicking outside, pressing ESC, close button or calling close() method
const CLOSED_BY_VALUES = ['none', 'closerequest', 'any'] as const

export type Args = {
    tag?: string
    header?: {
        title?: string
        iconClass?: string
        closeIconClass?: string
    }
    footer?: {
        justifyEnd?: boolean
        buttons?: ButtonArgs[]
    }
    innerHTML?: string
    closedby?: (typeof CLOSED_BY_VALUES)[number]
    dialogId?: string
    modal?: boolean
    defaultOpen?: boolean
    attributes?: Record<string, string>
}

const meta: Meta<Args> = {
    title: 'Components/Overlay/Dialog',
    tags: ['autodocs'],
    args: {
        tag: 'dialog',
        defaultOpen: false,
        header: {
            title: 'Dialog title',
            iconClass: 'rz-icon-ri--layout-4-line',
            closeIconClass: 'rz-icon-ri--close-line',
        },
        footer: {
            buttons: [
                {
                    label: 'Cancel',
                    emphasis: 'tertiary',
                    attributes: {
                        closetarget: '',
                    },
                },
                {
                    label: 'Confirm',
                    emphasis: 'primary',
                    attributes: {
                        closetarget: '',
                    },
                },
            ],
        },
        innerHTML: 'rz dialog body content',
        closedby: 'any',
        modal: true,
        dialogId: 'meta-default-dialog',
    },
    argTypes: {
        closedby: {
            control: { type: 'select' },
            options: CLOSED_BY_VALUES,
            description:
                '‘none’ = dialog cannot be closed by user interaction; ‘closerequest’ = dialog can be closed only by close button or calling close() method; ‘any’ = dialog can be closed by clicking outside, pressing ESC, close button or calling close() method',
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzDialogWrapperRenderer(args)
    },
    args: {
        dialogId: 'default-dialog',
    },
}

export const WithoutHead: Story = {
    render: (args) => {
        return rzDialogWrapperRenderer(args)
    },
    args: {
        header: undefined,
        dialogId: 'without-head-dialog',
    },
}

export const WithoutFooter: Story = {
    render: (args) => {
        return rzDialogWrapperRenderer(args)
    },
    args: {
        footer: undefined,
        dialogId: 'without-footer-dialog',
    },
}

export const FooterJustifyEnd: Story = {
    render: (args) => {
        return rzDialogWrapperRenderer(args)
    },
    args: {
        footer: {
            ...meta.args.footer,
            justifyEnd: true,
        },
        dialogId: 'footer-justify-end-dialog',
    },
}

export const WithForm: Story = {
    render: (args) => {
        return rzDialogWrapperRenderer(args, {
            label: 'Open form dialog',
        })
    },
    args: {
        dialogId: 'WithForm-dialog',
        innerHTML: `
			<div>
				<form method="dialog">
					<button>Close dialog from form 'method="dialog"'</button>
				</form>
				<form>
					<button formmethod="dialog">Close dialog from button 'formmethod="dialog"'</button>
				</form>
			</div>
		`,
        footer: undefined,
    },
}

export const NonModal: Story = {
    render: (args) => {
        const element = rzDialogWrapperRenderer(args, {
            label: 'Toggle non-modal dialog',
            attributes: {
                opentarget: undefined,
                toggletarget: args.dialogId,
            },
        })
        element.style.marginBlock = '100px'

        return element
    },
    args: {
        dialogId: 'non-modal-dialog',
        modal: false,
        closedby: 'none',
    },
}
