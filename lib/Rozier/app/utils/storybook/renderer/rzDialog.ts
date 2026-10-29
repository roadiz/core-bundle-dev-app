import {
    RzButtonOptions,
    rzButtonRenderer,
} from '~/utils/component-renderer/rzButton'
import type { Args } from '../../../../stories/RzDialog.stories'
import { rzElement } from '~/utils/component-renderer/rzElement'

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

function rzDialogHeaderRenderer(args: Args['header']) {
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
    args: NonNullable<Args['alert']>,
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

function rzDialogFooterRenderer(args: Args['footer']) {
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

export function rzDialogRenderer(args: Args) {
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
    dialogArgs: Args,
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
