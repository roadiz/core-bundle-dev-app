import {
    RZ_CODE_EDITOR_MODES,
    type RzCodeEditorMode,
} from '~/custom-elements/RzCodeEditor'

export { RZ_CODE_EDITOR_MODES }

export type RzCodeEditorOptions = {
    mode?: RzCodeEditorMode
    value?: string
    // Attributes of the textarea (name, id, disabled…)
    attributes?: Record<string, string | boolean>
}

const COMPONENT_CLASS_NAME = 'rz-code-editor'

export function rzCodeEditorRenderer(args: RzCodeEditorOptions) {
    const node = document.createElement('rz-code-editor')
    node.classList.add(COMPONENT_CLASS_NAME)
    node.setAttribute('mode', args.mode || 'yaml')

    const textarea = document.createElement('textarea')
    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (value === true) textarea.setAttribute(key, '')
        else if (value) textarea.setAttribute(key, value)
    })
    textarea.value = args.value || ''
    node.appendChild(textarea)

    return node
}
