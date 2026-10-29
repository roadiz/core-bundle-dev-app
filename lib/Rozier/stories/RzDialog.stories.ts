import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzDialogWrapperRenderer } from '~/utils/storybook/renderer/rzDialog'
import { type RzButtonOptions } from '~/utils/component-renderer/rzButton'
import { RzElement } from '~/utils/component-renderer/rzElement'

// 'none' = dialog cannot be closed by user interaction
// 'closerequest' = dialog can be closed only by close button or calling close() method
// 'any' = dialog can be closed by clicking outside, pressing ESC, close button or calling close() method
const CLOSED_BY_VALUES = ['none', 'closerequest', 'any'] as const
const VARIANTS = ['warning', 'danger'] as const

export type Args = {
    tag?: string
    header?: {
        title?: string
        iconClass?: string
        closeIconClass?: string
    }
    body?: RzElement
    /** Centered alert layout: status medallion, title and text, no header bar. */
    alert?: {
        iconClass: string
        title: string
        text: string
        closeIconClass?: string
    }
    variant?: (typeof VARIANTS)[number]
    footer?: {
        justifyEnd?: boolean
        buttons?: RzButtonOptions[]
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
        body: {
            innerHTML: 'rz dialog body content',
        },
        closedby: 'any',
        modal: true,
        dialogId: 'meta-default-dialog',
    },
    argTypes: {
        variant: {
            control: { type: 'radio' },
            options: VARIANTS,
        },
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

// Mirrors the session expired dialog of base.html.twig, opened by LoginCheckService.
export const SessionExpired: Story = {
    render: (args) => {
        return rzDialogWrapperRenderer(args)
    },
    args: {
        dialogId: 'session-expired-dialog',
        variant: 'warning',
        closedby: 'none',
        header: undefined,
        alert: {
            iconClass: 'rz-icon-ri--lock-line',
            title: 'Your session has expired',
            text: 'You have been logged out after a period of inactivity. Log in again to keep working, your unsaved changes will be lost.',
            closeIconClass: 'rz-icon-ri--close-line',
        },
        footer: {
            buttons: [
                {
                    label: 'Login',
                    iconClass: 'rz-icon-ri--arrow-right-s-line',
                    emphasis: 'primary',
                    size: 'lg',
                    tag: 'a',
                    attributes: { href: '#', autofocus: '' },
                },
            ],
        },
    },
}

// Mirrors the health check dialog of base.html.twig: no close button, no action.
export const HealthCheckFailed: Story = {
    render: (args) => {
        return rzDialogWrapperRenderer(args)
    },
    args: {
        dialogId: 'health-check-dialog',
        variant: 'danger',
        closedby: 'none',
        header: undefined,
        alert: {
            iconClass: 'rz-icon-ri--information-line',
            title: 'Back-office unreachable',
            text: 'The administration back-end stopped answering. Wait for it to come back, this dialog will close on its own.',
        },
        footer: undefined,
    },
}
