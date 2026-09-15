import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    SEVERITIES,
    type WarningDialogArgs,
    rzWarningDialogRenderer,
    rzWarningDialogWrapperRenderer,
} from '~/utils/storybook/renderer/rzWarningDialog'

export type Args = WarningDialogArgs

// Mirrors the two dialogs of includes/rz_warning_dialogs.html.twig, opened by LoginCheckService.
const SESSION_EXPIRED: Partial<Args> = {
    severity: 'warning',
    title: 'Your session has expired',
    content:
        'You have been logged out after a period of inactivity. Log in again to keep working, your unsaved changes will be lost.',
    iconClass: 'rz-icon-ri--lock-line',
    closeable: true,
    actionLabel: 'Login',
}

const HEALTH_CHECK_FAILED: Partial<Args> = {
    severity: 'danger',
    title: 'Back-office unreachable',
    content:
        'The administration back-end stopped answering. Wait for it to come back, this dialog will close on its own.',
    iconClass: 'rz-icon-ri--information-line',
    closeable: false,
}

const meta: Meta<Args> = {
    title: 'Components/Overlay/Warning dialog',
    tags: ['autodocs'],
    args: {
        ...SESSION_EXPIRED,
        dialogId: 'meta-warning-dialog',
    } as Args,
    argTypes: {
        severity: {
            control: { type: 'radio' },
            options: SEVERITIES,
        },
    },
}

export default meta
type Story = StoryObj<Args>

/** Both dialogs side by side, already open, to review the design at a glance. */
export const Overview: Story = {
    render: () => {
        const wrapper = document.createElement('div')
        wrapper.style.display = 'flex'
        wrapper.style.flexWrap = 'wrap'
        wrapper.style.alignItems = 'start'
        wrapper.style.gap = 'var(--spacing-lg)'
        wrapper.style.padding = 'var(--spacing-md)'

        ;[SESSION_EXPIRED, HEALTH_CHECK_FAILED].forEach((content, index) => {
            wrapper.appendChild(
                rzWarningDialogRenderer({
                    ...content,
                    inline: true,
                    dialogId: `overview-dialog-${index}`,
                } as Args),
            )
        })

        return wrapper
    },
}

/** Session expiration: dismissible through the close cross, primary action to log in again. */
export const SessionExpired: Story = {
    render: (args) => rzWarningDialogWrapperRenderer(args),
    args: { dialogId: 'session-expired-dialog' },
}

/** Health check failure: no close cross, no action, the user can only wait. */
export const HealthCheckFailed: Story = {
    render: (args) => rzWarningDialogWrapperRenderer(args),
    args: {
        ...HEALTH_CHECK_FAILED,
        dialogId: 'health-check-dialog',
    } as Args,
}
