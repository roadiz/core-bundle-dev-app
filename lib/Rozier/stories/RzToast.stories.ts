import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzToastRenderer,
    type ToastDetail,
} from '~/utils/component-renderer/rzToast'

type Args = ToastDetail

const meta: Meta<Args> = {
    title: 'Components/Feedback/Toast',
    tags: ['autodocs'],
    render: (args) => rzToastListRenderer(args),
    args: {
        id: 1,
        status: 'success',
        title: 'Success',
        iconClass: 'rz-icon rz-icon-ri--check-line',
        message: 'Your changes have been saved.',
    },
    argTypes: {
        status: {
            control: { type: 'select' },
            options: ['success', 'warning', 'danger'],
        },
    },
}

export default meta
type Story = StoryObj<Args>

function rzToastListRenderer(items: ToastDetail | ToastDetail[]) {
    const wrapper = document.createElement('rz-toast-list')
    wrapper.setAttribute('role', 'region')
    wrapper.setAttribute('aria-live', 'polite')
    wrapper.setAttribute('aria-atomic', 'false')

    const toasts = Array.isArray(items) ? items : [items]

    toasts.forEach((toastData) => {
        const toastElement = rzToastRenderer(toastData)
        wrapper.appendChild(toastElement)
    })

    return wrapper
}

export const Default: Story = {}

export const Warning: Story = {
    args: {
        id: 2,
        status: 'warning',
        title: 'Warning',
        iconClass: 'rz-icon rz-icon-ri--error-warning-line',
        message: 'This action will update existing data.',
    },
}

export const Danger: Story = {
    args: {
        id: 3,
        status: 'danger',
        title: 'Error',
        iconClass: 'rz-icon rz-icon-ri--prohibited-line',
        message: 'We could not save your changes.',
    },
}

/**
 * The first toast is driven by the controls, the next ones are fixed.
 */
export const Stacked: Story = {
    render: (args) => {
        const toasts = [
            args,
            {
                id: 11,
                status: 'warning' as const,
                title: 'Warning',
                iconClass: 'rz-icon rz-icon-ri--error-warning-line',
                message: 'Some fields still need attention.',
            },
            {
                id: 12,
                status: 'danger' as const,
                title: 'Error',
                iconClass: 'rz-icon rz-icon-ri--prohibited-line',
                message: 'We could not save your changes.',
            },
        ]

        return rzToastListRenderer(toasts)
    },
    args: {
        id: 10,
        message: 'Content published successfully.',
    },
}
