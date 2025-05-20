/**
 * Css Editor
 */
export default class CssEditor {
    /**
     * @param {HTMLTextAreaElement} textarea
     * @param index
     */
    constructor(textarea, index) {
        this.textarea = textarea
        this.cont = this.textarea.closest('.uk-form-row')
        this.settingRow = this.textarea.closest('.setting-row')
        this.tabSize = 4

        let options = {
            lineNumbers: true,
            mode: 'css',
            theme: 'mbo',
            tabSize: 4,
            indentWithTabs: false,
            lineWrapping: true,
            dragDrop: false,
            readOnly: this.textarea.hasAttribute('disabled') && this.textarea.getAttribute('disabled') === 'disabled',
            extraKeys: {
                Tab: (cm) => cm.execCommand('indentMore'),
                'Shift-Tab': (cm) => cm.execCommand('indentLess'),
            },
        }

        if (this.settingRow) {
            options.lineNumbers = false
        }

        this.editor = window.CodeMirror.fromTextArea(this.textarea, options)

        this.forceEditorUpdate = this.forceEditorUpdate.bind(this)
        this.textareaChange = this.textareaChange.bind(this)
        this.textareaFocus = this.textareaFocus.bind(this)
        this.textareaBlur = this.textareaBlur.bind(this)

        // Methods
        this.init()
    }

    init() {
        if (this.textarea) {
            this.editor.on('change', this.textareaChange)
            this.editor.on('focus', this.textareaFocus)
            this.editor.on('blur', this.textareaBlur)

            window.requestAnimationFrame(() => {
                const switchers = document.querySelectorAll('[data-uk-switcher]')
                switchers.forEach((switcher) => {
                    switcher.addEventListener('show.uk.switcher', this.forceEditorUpdate)
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
