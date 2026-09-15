import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzElement } from '~/utils/component-renderer/rzElement'

export const SEVERITIES = ['warning', 'danger'] as const

export type WarningDialogArgs = {
    dialogId: string
    severity: (typeof SEVERITIES)[number]
    title: string
    content: string
    iconClass: string
    /** Session expiration can be dismissed, a health check failure cannot. */
    closeable?: boolean
    actionLabel?: string
    /** Render inline and already open, to see the dialog without opening it. */
    inline?: boolean
}

function closeRenderer() {
    const close = rzButtonRenderer({
        iconClass: 'rz-icon-ri--close-line',
        size: 'sm',
        emphasis: 'tertiary',
        attributes: {
            'aria-label': 'Close dialog',
            type: 'button',
            closetarget: '',
        },
    })
    close.classList.add('rz-dialog__close')

    return close
}

function bodyRenderer(args: WarningDialogArgs) {
    const body = rzElement({ attributes: { class: 'rz-dialog__body' } })

    const medallion = rzElement({
        tag: 'span',
        attributes: { class: 'rz-dialog__medallion' },
    })
    medallion.appendChild(
        rzElement({ tag: 'span', attributes: { class: args.iconClass } }),
    )
    body.appendChild(medallion)

    const group = rzElement({ attributes: { class: 'rz-dialog__group' } })
    group.appendChild(
        rzElement({
            tag: 'h1',
            innerText: args.title,
            attributes: { class: 'rz-dialog__title' },
        }),
    )
    group.appendChild(
        rzElement({
            tag: 'p',
            innerText: args.content,
            attributes: { class: 'rz-dialog__text' },
        }),
    )
    body.appendChild(group)

    return body
}

function footerRenderer(args: WarningDialogArgs) {
    if (!args.actionLabel) return null

    const footer = rzElement({
        tag: 'footer',
        attributes: { class: 'rz-dialog__footer' },
    })

    footer.appendChild(
        rzButtonRenderer({
            label: args.actionLabel,
            iconClass: 'rz-icon-ri--arrow-right-s-line',
            emphasis: 'primary',
            size: 'lg',
            tag: 'a',
            is: 'rz-link',
            attributes: { href: '#', autofocus: '' },
        }),
    )

    return footer
}

export function rzWarningDialogRenderer(args: WarningDialogArgs) {
    const dialog = document.createElement('dialog', { is: 'rz-dialog' })
    dialog.setAttribute('is', 'rz-dialog')
    dialog.setAttribute('id', args.dialogId)
    dialog.setAttribute('modal', args.inline ? 'false' : 'true')
    dialog.setAttribute('closedby', 'none')
    dialog.classList.add('rz-dialog', 'rz-dialog--warning')

    if (args.severity === 'danger') {
        dialog.classList.add('rz-dialog--danger')
    }

    if (args.inline) {
        dialog.setAttribute('defaultopen', 'true')
        // An open dialog is position:absolute in the UA stylesheet, even non-modal:
        // put it back in the flow so several of them can be laid out side by side.
        dialog.style.position = 'relative'
        dialog.style.margin = '0'
    }

    if (args.closeable) {
        dialog.appendChild(closeRenderer())
    }

    dialog.appendChild(bodyRenderer(args))

    const footer = footerRenderer(args)
    if (footer) dialog.appendChild(footer)

    return dialog
}

export function rzWarningDialogWrapperRenderer(args: WarningDialogArgs) {
    const wrapper = document.createElement('div')
    wrapper.appendChild(rzWarningDialogRenderer(args))
    wrapper.appendChild(
        rzButtonRenderer({
            label: 'Open dialog',
            emphasis: 'primary',
            attributes: { opentarget: args.dialogId, type: 'button' },
        }),
    )

    return wrapper
}
