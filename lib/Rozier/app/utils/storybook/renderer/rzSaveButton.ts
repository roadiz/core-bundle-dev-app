import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

export type RzSaveButtonOptions = {
    /** Selector of the form submitted on click and on Cmd/Ctrl+S. */
    dataActionSave: string
    label?: string
}

/** Mirrors rz_actions_menu.saveItem (components/rz_actions_menu.html.twig). */
export function rzSaveButtonRenderer(args: RzSaveButtonOptions) {
    return rzButtonRenderer({
        is: 'rz-save-button',
        size: 'md',
        onDark: true,
        emphasis: 'primary',
        color: 'success',
        iconClass: 'rz-icon-ri--save-line',
        attributes: {
            'data-action-save': args.dataActionSave,
            type: 'submit',
            title: args.label || 'Save',
        },
    })
}
