import {
    rzToastRenderer,
    type ToastDetail,
    RZ_TOAST_LIST_ID,
} from '~/utils/component-renderer/rzToast'

const DEFAULT_TIMEOUT = 3000

type ToastItem = {
    id: number
    element: HTMLElement
    timeoutId: number | null
}

export default class RzToastList extends HTMLElement {
    private toasts: Map<number, ToastItem> = new Map()
    private toastId = 0

    constructor() {
        super()

        console.log('rz-toast-list initialized')
        this.onPushToast = this.onPushToast.bind(this)
        this.onCommand = this.onCommand.bind(this)
    }

    get elementId() {
        return this.id || RZ_TOAST_LIST_ID
    }

    onCommand(event: CommandEvent) {
        if (event.command === '--close-toast') {
            this.onCloseToast(event)
        }
    }

    onCloseToast(event: CommandEvent) {
        const toastElement = event.source.closest(
            '.rz-toast[data-toast-id]',
        ) as HTMLElement | null

        const toastId = Number(toastElement?.dataset.toastId)
        if (typeof toastId === 'number' && !isNaN(toastId)) {
            this.removeToast(toastId)
        }
    }

    connectedCallback() {
        if (!this.id) {
            this.id = this.elementId
        }

        this.setAttribute('role', 'region')
        this.setAttribute('aria-atomic', 'false')
        if (!this.hasAttribute('aria-live')) {
            this.setAttribute('aria-live', 'polite')
        }

        this.addEventListener('command', this.onCommand)
        window.addEventListener('pushToast', this.onPushToast)
        this.flushQueuedToasts()
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
        window.removeEventListener('pushToast', this.onPushToast)
        this.clearAllTimeouts()
        this.toasts.clear()
    }

    pushToast(detail: ToastDetail) {
        const timeout =
            typeof detail.timeout === 'number'
                ? detail.timeout
                : DEFAULT_TIMEOUT

        const toastId = this.toastId++

        const toastElement = rzToastRenderer({
            id: toastId,
            message: detail.message,
            status: detail.status || 'success',
            title: detail.title,
            iconClass: detail.iconClass,
            commandfor: detail.commandfor || this.elementId,
        })

        const timeoutId =
            timeout > 0
                ? window.setTimeout(() => this.removeToast(toastId), timeout)
                : null

        this.toasts.set(toastId, {
            id: toastId,
            element: toastElement,
            timeoutId,
        })
        this.prepend(toastElement)
    }

    private onPushToast(event: Event) {
        const toastEvent = event as CustomEvent<ToastDetail>
        const detail = toastEvent.detail

        console.log('rz-toast-list received pushToast event', event, detail)

        this.pushToast({
            message: detail.message,
            status: detail.status || 'success',
            timeout: detail.timeout,
        })
    }

    private flushQueuedToasts() {
        const queue = (
            window as typeof window & { __rzToastQueue?: ToastDetail[] }
        ).__rzToastQueue

        if (!queue || queue.length === 0) {
            return
        }

        queue.forEach((detail) => this.pushToast(detail))
        queue.length = 0
    }

    private clearAllTimeouts() {
        this.toasts.forEach((toast) => {
            if (toast.timeoutId !== null) {
                window.clearTimeout(toast.timeoutId)
            }
        })
    }

    private removeToast(id: number) {
        const toast = this.toasts.get(id)
        if (!toast) {
            return
        }

        if (toast.timeoutId !== null) {
            window.clearTimeout(toast.timeoutId)
        }

        toast.element.classList.add('rz-toast--leaving')
        toast.element.addEventListener(
            'animationend',
            () => {
                toast.element.remove()
                this.toasts.delete(id)
            },
            { once: true },
        )
    }
}
