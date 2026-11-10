import type { EditorConfiguration, EditorFromTextArea } from 'codemirror'
import CodeMirror from 'codemirror'
import 'codemirror/mode/javascript/javascript'
import 'codemirror/mode/css/css'
import 'codemirror/mode/yaml/yaml'
import 'codemirror/addon/display/rulers'

const TAB_SIZE = 4
const YAML_RULER_COUNT = 18

const MODES = {
    json: { name: 'javascript', json: true },
    css: 'css',
    yaml: 'yaml',
} satisfies Record<string, EditorConfiguration['mode']>

export type RzCodeEditorMode = keyof typeof MODES

export const RZ_CODE_EDITOR_MODES = Object.keys(MODES) as RzCodeEditorMode[]

const DEFAULT_MODE: RzCodeEditorMode = 'yaml'

type Ruler = {
    column: number
    lineStyle: string
    width: string
    color: string
}

/**
 * Turns its textarea into a CodeMirror editor for JSON, CSS or YAML.
 *
 * <rz-code-editor class="rz-code-editor" mode="yaml">
 *     <textarea name="…"></textarea>
 * </rz-code-editor>
 */
export default class RzCodeEditor extends HTMLElement {
    editor: EditorFromTextArea | null = null

    constructor() {
        super()

        this.refresh = this.refresh.bind(this)
    }

    get mode(): RzCodeEditorMode {
        const mode = this.getAttribute('mode')
        if (mode !== null && mode in MODES) return mode as RzCodeEditorMode

        console.warn(
            `Unknown rz-code-editor mode "${mode}", falling back to "${DEFAULT_MODE}"`,
        )
        return DEFAULT_MODE
    }

    connectedCallback() {
        // Moved in the DOM (e.g. rz-repeatable reordering): keep the editor
        if (this.editor) {
            window.requestAnimationFrame(this.refresh)
            return
        }

        // The textarea child must exist on connection: true today because the
        // element is defined lazily (after parsing) or inserted as a parsed
        // fragment. Defining it eagerly in the document head would break this.
        const textarea =
            this.querySelector<HTMLTextAreaElement>(':scope > textarea')
        if (!textarea) {
            console.error('No textarea found in rz-code-editor')
            return
        }

        this.editor = CodeMirror.fromTextArea(
            textarea,
            this.editorConfiguration(textarea),
        )
        // Keep the textarea value in sync for scripts reading it before submission
        this.editor.on('change', (editor) => {
            ;(editor as EditorFromTextArea).save()
        })

        // CodeMirror needs the element laid out to measure its lines
        window.requestAnimationFrame(this.refresh)
    }

    disconnectedCallback() {
        // A move disconnects and reconnects within the same task: only tear
        // the editor down when the element really left the document.
        queueMicrotask(() => {
            if (this.isConnected || !this.editor) return

            this.editor.toTextArea()
            this.editor = null
        })
    }

    refresh() {
        this.editor?.refresh()
    }

    editorConfiguration(textarea: HTMLTextAreaElement): EditorConfiguration {
        const mode = this.mode
        const options: EditorConfiguration & { rulers?: Ruler[] } = {
            mode: MODES[mode],
            theme: 'rz',
            // Settings rows are too narrow for a gutter
            lineNumbers: !textarea.closest('.setting-row'),
            tabSize: TAB_SIZE,
            indentUnit: TAB_SIZE,
            indentWithTabs: false,
            lineWrapping: true,
            dragDrop: false,
            readOnly: textarea.disabled,
            extraKeys: {
                Tab: 'indentMore',
                'Shift-Tab': 'indentLess',
            },
        }

        if (mode === 'yaml') {
            options.rulers = Array.from(
                { length: YAML_RULER_COUNT },
                (_, i) => ({
                    column: TAB_SIZE * (i + 1),
                    lineStyle: 'dashed',
                    width: '1px',
                    color: 'var(--line-on-light-primary)',
                }),
            )
        }

        return options
    }
}
