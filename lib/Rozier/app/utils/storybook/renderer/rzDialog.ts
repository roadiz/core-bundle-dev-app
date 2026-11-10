import {
    RzButtonOptions,
    rzButtonRenderer,
} from '~/utils/component-renderer/rzButton'
import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

// 'none' = dialog cannot be closed by user interaction
// 'closerequest' = dialog can be closed only by close button or calling close() method
// 'any' = dialog can be closed by clicking outside, pressing ESC, close button or calling close() method
export const CLOSED_BY_VALUES = ['none', 'closerequest', 'any'] as const
export const VARIANTS = ['warning', 'danger'] as const

export type RzDialogOptions = {
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

function rzDialogCloseRenderer(closeIconClass: string) {
    const button = rzButtonRenderer({
        iconClass: closeIconClass,
        size: 'sm',
        emphasis: 'tertiary',
        attributes: {
            'aria-label': 'Close dialog',
            autofocus: '',
            closetarget: '',
        },
    })
    button.classList.add('rz-dialog__close')

    return button
}

function rzDialogHeaderRenderer(args: RzDialogOptions['header']) {
    const wrapper = document.createElement('header')
    wrapper.classList.add('rz-dialog__header')

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add('rz-dialog__icon', args.iconClass)
        wrapper.appendChild(icon)
    }

    if (args.title) {
        const title = document.createElement('h1')
        title.classList.add('rz-dialog__title')
        title.innerText = args.title
        wrapper.appendChild(title)
    }

    if (args.closeIconClass) {
        wrapper.appendChild(rzDialogCloseRenderer(args.closeIconClass))
    }

    return wrapper
}

function rzDialogAlertRenderer(
    args: NonNullable<RzDialogOptions['alert']>,
    idPrefix: string,
) {
    const medallion = rzElement({
        tag: 'span',
        attributes: { class: 'rz-dialog__medallion' },
    })
    medallion.appendChild(
        rzElement({
            tag: 'span',
            attributes: { class: args.iconClass, 'aria-hidden': 'true' },
        }),
    )

    const group = rzElement({ attributes: { class: 'rz-dialog__group' } })
    group.appendChild(
        rzElement({
            tag: 'h1',
            innerText: args.title,
            attributes: { class: 'rz-dialog__title', id: `${idPrefix}-title` },
        }),
    )
    group.appendChild(
        rzElement({
            tag: 'p',
            innerText: args.text,
            attributes: { class: 'rz-dialog__text', id: `${idPrefix}-text` },
        }),
    )

    return [medallion, group]
}

function rzDialogFooterRenderer(args: RzDialogOptions['footer']) {
    if (!args?.buttons?.length) {
        return
    }
    const wrapper = document.createElement('footer')
    wrapper.classList.add('rz-dialog__footer')

    if (args.justifyEnd) {
        wrapper.classList.add('rz-dialog__footer--justify-end')
    }

    // Footer actions close the dialog, unless a button opts out with closetarget: undefined
    args?.buttons?.forEach((buttonArgs) => {
        const button = rzButtonRenderer({
            ...buttonArgs,
            attributes: { closetarget: '', ...buttonArgs.attributes },
        })
        wrapper.appendChild(button)
    })

    return wrapper
}

export function rzDialogRenderer(args: RzDialogOptions) {
    const dialog = document.createElement('dialog', { is: 'rz-dialog' })
    dialog.setAttribute('is', 'rz-dialog')
    dialog.classList.add('rz-dialog')
    dialog.id = args.dialogId

    const attributesEntries = Object.entries(args.attributes || {})
    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            if (typeof value === 'undefined') return
            dialog.setAttribute(key, value)
        })
    }

    if (args.modal) {
        dialog.setAttribute('modal', args.modal?.toString())
    }

    if (args.closedby) {
        dialog.setAttribute('closedby', args.closedby || 'any')
    }

    if (args.variant) {
        dialog.classList.add('rz-dialog--warning')
        if (args.variant === 'danger') dialog.classList.add('rz-dialog--danger')
    }

    if (args?.header) {
        const header = rzDialogHeaderRenderer(args.header)
        dialog.appendChild(header)
    }

    if (args.alert?.closeIconClass) {
        dialog.appendChild(rzDialogCloseRenderer(args.alert.closeIconClass))
    }

    const body = rzElement({
        tag: 'div',
        ...args?.body,
        attributes: {
            ...(args.body?.attributes || {}),
            class: `rz-dialog__body ${args.body?.attributes?.class || ''}`,
        },
    })
    if (args.alert) {
        const idPrefix = args.dialogId || 'rz-dialog'
        dialog.setAttribute('aria-labelledby', `${idPrefix}-title`)
        dialog.setAttribute('aria-describedby', `${idPrefix}-text`)
        body.append(...rzDialogAlertRenderer(args.alert, idPrefix))
    }
    dialog.appendChild(body)

    const footer = rzDialogFooterRenderer(args.footer)
    if (footer) {
        dialog.appendChild(footer)
    }
    return dialog
}

export function rzDialogWrapperRenderer(
    dialogArgs: RzDialogOptions,
    target: Partial<RzButtonOptions> = {},
) {
    const wrapper = document.createElement('div')
    const dialog = rzDialogRenderer(dialogArgs)
    wrapper.appendChild(dialog)

    const button = rzButtonRenderer({
        label: 'Open dialog',
        emphasis: 'primary',
        ...target,
        attributes: {
            opentarget: dialogArgs.dialogId,
            ...target.attributes,
        },
    })
    wrapper.appendChild(button)

    return wrapper
}
