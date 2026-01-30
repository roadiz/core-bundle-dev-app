import { rzBadgeRenderer } from '~/utils/component-renderer/rzBadge'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

export type ToastStatus = 'success' | 'warning' | 'danger'

export type ToastDetail = {
    message: string // Accepts HTML content
    id?: number
    title?: string
    iconClass?: string
    status?: ToastStatus
    timeout?: number
    commandfor?: string
}

const STATUS_CONFIG: Record<ToastStatus, { title: string; iconClass: string }> =
    {
        success: {
            title: 'Success',
            iconClass: 'rz-icon-ri--check-line',
        },
        warning: {
            title: 'Warning',
            iconClass: 'rz-icon-ri--error-warning-line',
        },
        danger: {
            title: 'Error',
            iconClass: 'rz-icon-ri--prohibited-line',
        },
    }

export const RZ_TOAST_LIST_ID = 'rz-toast-list'

export function rzToastRenderer(options: ToastDetail) {
    const {
        id,
        message,
        status = 'success',
        title = '',
        iconClass = '',
        commandfor = RZ_TOAST_LIST_ID,
    } = options

    const toast = document.createElement('div')
    toast.className = `rz-toast rz-toast--${status}`
    toast.setAttribute('role', status === 'danger' ? 'alert' : 'status')
    toast.dataset.toastId = String(id)

    const iconWrap = rzBadgeRenderer({
        iconClass: iconClass || STATUS_CONFIG[status].iconClass,
        size: 'sm',
        color: status,
        attributes: {
            'aria-hidden': 'true',
        },
    })
    iconWrap.classList.add('rz-toast__icon')

    const titleElement = document.createElement('span')
    titleElement.className = 'rz-toast__title'
    titleElement.textContent = title || STATUS_CONFIG[status].title

    const closeButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--close-line',
        emphasis: 'tertiary',
        size: 'xs',
        attributes: {
            type: 'button',
            'aria-label': 'Close notification',
            commandfor,
            command: '--close-toast',
        },
    })
    closeButton.classList.add('rz-toast__close')
    toast.appendChild(closeButton)

    toast.appendChild(iconWrap)
    toast.appendChild(titleElement)
    toast.appendChild(closeButton)

    const content = document.createElement('div')
    content.className = 'rz-toast__content'
    content.innerHTML = message

    toast.appendChild(content)

    return toast
}
