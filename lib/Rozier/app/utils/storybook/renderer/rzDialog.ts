import type { Args } from '../../../../stories/RzDialog.stories'
import type { ButtonArgs } from '../../../../stories/RzButton.stories'
import { rzButtonRenderer } from '~/utils/storybook/renderer/rzButton'

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
        const button = rzButtonRenderer({
            iconClass: args.closeIconClass,
            size: 'sm',
            emphasis: 'tertiary',
            attributes: {
                'aria-label': 'Close dialog',
                autofocus: '',
                closetarget: '',
            },
        })
        button.classList.add('rz-dialog__close')
        wrapper.appendChild(button)
    }

    return wrapper
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

    args?.buttons?.forEach((buttonArgs) => {
        const button = rzButtonRenderer(buttonArgs)
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

    if (args?.header) {
        const header = rzDialogHeaderRenderer(args.header)
        dialog.appendChild(header)
    }

    const body = document.createElement('div')
    body.classList.add('rz-dialog__body')
    body.innerHTML = args.innerHTML || ''
    dialog.appendChild(body)

    const footer = rzDialogFooterRenderer(args.footer)
    if (footer) {
        dialog.appendChild(footer)
    }
    return dialog
}

export function rzDialogWrapperRenderer(
    dialogArgs: Args,
    target: Partial<ButtonArgs> = {},
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
