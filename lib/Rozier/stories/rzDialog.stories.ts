import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/storybook/renderer/rzButton'
import type { ButtonArgs } from './RzButton.stories'

type Header = {
    title?: string
    iconClass?: string
    closeIconClass?: string
}

type Footer = {
    buttons?: ButtonArgs[]
}

// 'none' = dialog cannot be closed by user interaction
// 'closerequest' = dialog can be closed only by close button or calling close() method
// 'any' = dialog can be closed by clicking outside, pressing ESC, close button or calling close() method
const CLOSED_BY_VALUES = ['none', 'closerequest', 'any'] as const

export type Args = {
    tag?: string
    header?: Header
    footer?: Footer
    innerHTML?: string
    closedby: (typeof CLOSED_BY_VALUES)[number]
    nonModal?: boolean
}

const meta: Meta<Args> = {
    title: 'Components/Dialog',
    tags: ['autodocs'],
    args: {
        tag: 'dialog',
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
                        'rz-dialog-close-target': '',
                    },
                },
                {
                    label: 'Confirm',
                    emphasis: 'primary',
                    attributes: {
                        'rz-dialog-close-target': '',
                    },
                },
            ],
        },
        innerHTML: 'rz dialog body content',
        closedby: 'any',
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

function rzDialogHeaderRenderer(args: Header) {
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
                'rz-dialog-close-target': '',
            },
        })
        button.classList.add('rz-dialog__close')
        wrapper.appendChild(button)
    }

    return wrapper
}

function rzDialogFooterRenderer(args: Footer) {
    if (!args?.buttons?.length) {
        return
    }
    const wrapper = document.createElement('footer')
    wrapper.classList.add('rz-dialog__footer')

    args?.buttons?.forEach((buttonArgs) => {
        const button = rzButtonRenderer(buttonArgs)
        wrapper.appendChild(button)
    })

    return wrapper
}

function rzDialogRenderer(args: Args) {
    const wrapper = document.createElement('dialog', { is: 'rz-dialog' })
    wrapper.setAttribute('is', 'rz-dialog')

    wrapper.classList.add('rz-dialog')
    if (args.nonModal) {
        wrapper.setAttribute('rz-dialog-non-modal', args.nonModal?.toString())
    }

    if (args.closedby) {
        wrapper.setAttribute('closedby', args.closedby || 'any')
    }

    const header = rzDialogHeaderRenderer(args.header)
    wrapper.appendChild(header)

    const body = document.createElement('div')
    body.classList.add('rz-dialog__body')
    body.innerHTML = args.innerHTML || ''
    wrapper.appendChild(body)

    const footer = rzDialogFooterRenderer(args.footer)
    if (footer) {
        wrapper.appendChild(footer)
    }
    return wrapper
}

let defaultStoryCount = 0
export const Default: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        const dialog = rzDialogRenderer(args)
        const id = `default-dialog-${defaultStoryCount++}`
        dialog.id = id
        wrapper.appendChild(dialog)

        const button = rzButtonRenderer({
            label: 'Open dialog',
            emphasis: 'primary',
            attributes: {
                'rz-dialog-open-target': id,
            },
        })
        wrapper.appendChild(button)

        return wrapper
    },
}

export const WithForm: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        const dialog = rzDialogRenderer(args)
        const id = 'dialog-with-form'
        dialog.id = id
        wrapper.appendChild(dialog)

        const button = rzButtonRenderer({
            label: 'Open non modal dialog',
            emphasis: 'primary',
            attributes: {
                'rz-dialog-open-target': id,
            },
        })
        wrapper.appendChild(button)

        return wrapper
    },
    args: {
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
        const wrapper = document.createElement('div')
        const dialog = rzDialogRenderer(args)
        const id = 'non-modal-dialog'
        dialog.id = id
        wrapper.appendChild(dialog)

        const button = rzButtonRenderer({
            label: 'Open dialog',
            emphasis: 'primary',
            attributes: {
                'rz-dialog-open-target': id,
            },
        })
        wrapper.appendChild(button)

        return wrapper
    },
    args: {
        nonModal: true,
        closedby: 'none',
    },
}
