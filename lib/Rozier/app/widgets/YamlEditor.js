/**
 * Yaml editor
 */
export default class YamlEditor {
    /**
     * @param {HTMLTextAreaElement} textarea
     * @param index
     */
    constructor(textarea) {
        this.textarea = textarea
        this.cont = this.textarea.closest('.uk-form-row')
        this.settingRow = this.textarea.closest('.setting-row')
        this.tabSize = 4

        let rulers = []
        for (let i = 1; i < 19; i++) {
            rulers.push({
                column: this.tabSize * i,
                lineStyle: 'dashed',
                width: '1px',
                color: 'rgba(0,255,255,0.1)',
            })
        }

        let options = {
            lineNumbers: true,
            mode: 'yaml',
            theme: 'mbo',
            tabSize: this.tabSize,
            indentUnit: this.tabSize,
            indentWithTabs: false,
            lineWrapping: true,
            rulers: rulers,
            smartIndent: true,
            dragDrop: false,
            readOnly:
                this.textarea.hasAttribute('disabled') &&
                this.textarea.getAttribute('disabled') === 'disabled',
            extraKeys: {
                Tab: 'indentMore',
                'Shift-Tab': 'indentLess',
            },
        }

        if (this.settingRow) {
            options.lineNumbers = false
        }

        this.editor = window.CodeMirror.fromTextArea(this.textarea, options)

        // Bind methods
        this.textareaChange = this.textareaChange.bind(this)
        this.textareaFocus = this.textareaFocus.bind(this)
        this.textareaBlur = this.textareaBlur.bind(this)
        this.forceEditorUpdate = this.forceEditorUpdate.bind(this)

        // Init
        this.init()
    }

    init() {
        if (this.textarea) {
            this.editor.on('change', this.textareaChange)
            this.editor.on('focus', this.textareaFocus)
            this.editor.on('blur', this.textareaBlur)

            window.requestAnimationFrame(() => {
                const switchers =
                    document.querySelectorAll('[data-uk-switcher]')
                switchers.forEach((switcher) => {
                    switcher.addEventListener(
                        'show.uk.switcher',
                        this.forceEditorUpdate,
                    )
                })
                this.forceEditorUpdate()
            })
        }
    }

    forceEditorUpdate() {
        this.editor.refresh()
    }

    /**
     * Textarea change
     */
    textareaChange() {
        this.editor.save()
    }

    /**
     * Textarea focus
     */
    textareaFocus() {
        this.cont.classList.add('form-col-focus')
    }

    /**
     * Textarea focus out
     */
    textareaBlur() {
        this.cont.classList.remove('form-col-focus')
    }

    destroy() {}

    /**
     * Window resize callback
     */
    resize() {}
}
