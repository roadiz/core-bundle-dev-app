import {
    type RzButtonOptions,
    rzButtonRenderer,
} from '~/utils/component-renderer/rzButton'

export type RzBulkActionsItemOptions = {
    button: RzButtonOptions
    /** HTML of the panel the button toggles (nodes/bulkActions.html.twig `panels` block). */
    panel?: string
}

export type RzBulkActionsOptions = {
    title?: string
    /** Shown by rz-bulk-section once an item is selected. */
    visible?: boolean
    actions?: RzBulkActionsItemOptions[]
    deleteFormAction?: string
    /** Name of the hidden input rz-bulk-section fills with the selection (JSON). */
    deleteFormInputName?: string
}

const COMPONENT_CLASS_NAME = 'rz-bulk-actions'

/** Same buttons as nodes/bulkActions.html.twig: tags and status toggle a panel. */
export const DEFAULT_BULK_ACTIONS: RzBulkActionsOptions = {
    title: 'Bulk actions',
    deleteFormAction: '#',
    deleteFormInputName: 'nodes',
    actions: [
        {
            button: {
                emphasis: 'primary',
                iconClass: 'rz-icon-ri--price-tag-3-line',
                attributes: { type: 'button', title: 'Link tags' },
            },
            panel: '<p>Tags form</p>',
        },
        {
            button: {
                emphasis: 'primary',
                iconClass: 'rz-icon-ri--flag-line',
                attributes: { type: 'button', title: 'Nodes status' },
            },
            panel: '<p>Status form</p>',
        },
    ],
}

let instanceCounter = 0

/** Mirrors components/rz_bulk_actions.html.twig. */
export function rzBulkActionsRenderer(args: RzBulkActionsOptions) {
    const prefix = `${COMPONENT_CLASS_NAME}-${++instanceCounter}`
    const element = document.createElement('rz-bulk-actions')
    element.classList.add(COMPONENT_CLASS_NAME)
    element.setAttribute('visibility-class', `${COMPONENT_CLASS_NAME}--visible`)
    if (args.visible) {
        element.classList.add(`${COMPONENT_CLASS_NAME}--visible`)
    }

    const title = document.createElement('h3')
    title.classList.add(`${COMPONENT_CLASS_NAME}__title`)
    title.textContent = args.title || 'Bulk actions'
    element.appendChild(title)

    const buttons = document.createElement('div')
    buttons.classList.add(`${COMPONENT_CLASS_NAME}__buttons`)
    element.appendChild(buttons)

    buttons.appendChild(
        rzButtonRenderer({
            emphasis: 'primary',
            iconClass: 'rz-icon-ri--checkbox-blank-line',
            attributes: {
                type: 'button',
                title: 'Deselect all',
                'data-bulk-deselect-all-button': '',
            },
        }),
    )

    const panels: HTMLElement[] = []
    args.actions?.forEach((action, index) => {
        const attributes = { ...action.button.attributes }
        if (action.panel) {
            const panel = document.createElement('div')
            panel.id = `${prefix}-panel-${index}`
            panel.classList.add(`${COMPONENT_CLASS_NAME}__group`)
            panel.innerHTML = action.panel
            panels.push(panel)

            Object.assign(attributes, {
                'data-tab-controls': panel.id,
                'data-tab-panel-active-class': `${COMPONENT_CLASS_NAME}__group--visible`,
                'data-tab-active-class': 'rz-button--selected',
            })
        }
        buttons.appendChild(rzButtonRenderer({ ...action.button, attributes }))
    })

    if (args.deleteFormAction) {
        const form = document.createElement('form')
        form.classList.add(`${COMPONENT_CLASS_NAME}__form-button`)
        form.action = args.deleteFormAction
        form.method = 'POST'

        if (args.deleteFormInputName) {
            const input = document.createElement('input')
            input.type = 'hidden'
            input.classList.add('bulk-form-value')
            input.name = args.deleteFormInputName
            form.appendChild(input)
        }

        form.appendChild(
            rzButtonRenderer({
                color: 'danger',
                emphasis: 'primary',
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                attributes: { type: 'submit', title: 'Delete' },
            }),
        )
        buttons.appendChild(form)
    }

    panels.forEach((panel) => element.appendChild(panel))

    return element
}
