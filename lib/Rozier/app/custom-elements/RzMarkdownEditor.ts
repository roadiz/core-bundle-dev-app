import { stripTags } from '~/utils/plugins'
import markdownit from 'markdown-it'
import markdownItFootnote from 'markdown-it-footnote'
import type {
    Editor,
    EditorConfiguration,
    EditorFromTextArea,
} from 'codemirror'
import CodeMirror from 'codemirror'
import 'codemirror/mode/gfm/gfm'
import 'codemirror/mode/markdown/markdown'
// already load in main.js
// import 'assets/css/vendor/codemirror.css'

interface TranslationResponse {
    originalText: string
    translatedText: string
    sourceLang: string
    targetLang: string
}

interface DocumentUploadResponse {
    success: boolean
    document: {
        name: string
        url: string
    }
}

type ExtendedEditorConfiguration = EditorConfiguration & {
    enterMode?: string
    styleActiveLine?: boolean
}

export default class RzMarkdownEditor extends HTMLElement {
    markdownit!: markdownit
    textarea!: HTMLTextAreaElement
    usePreview: boolean = false
    editor!: EditorFromTextArea
    cont: HTMLElement | null = null
    buttonCode: NodeListOf<HTMLElement> | null = null
    buttonPreview: NodeListOf<HTMLElement> | null = null
    buttonTranslateAssistant: NodeListOf<HTMLElement> | null = null
    buttonTranslateAssistantRephrase: NodeListOf<HTMLElement> | null = null
    buttonFullscreen: NodeListOf<HTMLElement> | null = null
    fullscreenActive: boolean = false
    $editor!: HTMLElement
    buttons: NodeListOf<HTMLElement> | null = null
    content: NodeListOf<HTMLElement> | null = null
    tabs!: HTMLDivElement
    previewContainer!: HTMLElement
    preview!: HTMLDivElement
    refreshPreviewTimeout: number | null = null
    index: number = 0
    changeEvent: CustomEvent

    constructor() {
        super()

        this.changeEvent = new CustomEvent('change', {
            detail: { target: this },
        })
    }

    connectedCallback() {
        if (this.getAttribute('initialized') === 'true') {
            return
        }

        this.markdownit = new markdownit()
        this.markdownit.use(markdownItFootnote)

        const textarea = this.querySelector<HTMLTextAreaElement>('textarea')

        if (!textarea) {
            console.error('No textarea found in RzMarkdownEditor')
            return
        }

        this.textarea = textarea
        this.usePreview = false

        const editorConfig: ExtendedEditorConfiguration = {
            mode: 'gfm',
            lineNumbers: false,
            tabSize: 4,
            styleActiveLine: true,
            indentWithTabs: false,
            lineWrapping: true,
            viewportMargin: Infinity,
            enterMode: 'keep',
            direction:
                this.textarea.hasAttribute('dir') &&
                this.textarea.getAttribute('dir') === 'rtl'
                    ? 'rtl'
                    : 'ltr',
            readOnly:
                this.textarea.hasAttribute('disabled') &&
                this.textarea.getAttribute('disabled') === 'disabled',
        }

        this.editor = CodeMirror.fromTextArea(this.textarea, editorConfig)

        this.editor.addKeyMap({
            'Ctrl-B': (cm: Editor) => {
                cm.replaceSelections(this.boldSelections())
            },
            'Ctrl-I': (cm: Editor) => {
                cm.replaceSelections(this.italicSelections())
            },
            'Cmd-B': (cm: Editor) => {
                cm.replaceSelections(this.boldSelections())
            },
            'Cmd-I': (cm: Editor) => {
                cm.replaceSelections(this.italicSelections())
            },
        })

        // Selectors
        this.cont = this.textarea.closest('.rz-form-field')

        // Bind methods
        this.closePreview = this.closePreview.bind(this)
        this.onEditorChange = this.onEditorChange.bind(this)
        this.textareaFocus = this.textareaFocus.bind(this)
        this.textareaBlur = this.textareaBlur.bind(this)
        this.onDropFile = this.onDropFile.bind(this)
        this.buttonPreviewClick = this.buttonPreviewClick.bind(this)
        this.buttonClick = this.buttonClick.bind(this)
        this.forceEditorUpdate = this.forceEditorUpdate.bind(this)
        this.buttonTranslateAssistantClick =
            this.buttonTranslateAssistantClick.bind(this)
        this.buttonTranslateAssistantRephraseClick =
            this.buttonTranslateAssistantRephraseClick.bind(this)

        // Methods
        this.init()
    }

    disconnectedCallback() {
        this.destroy()
    }

    init() {
        if (!this.cont || !this.textarea) {
            return
        }

        const editorElement =
            this.cont.querySelector<HTMLElement>('.CodeMirror')

        if (!editorElement) {
            console.error('No CodeMirror element found')
            return
        }
        this.$editor = editorElement

        this.cont.classList.add('markdown-editor')
        if (this.editor.getOption('readOnly') === true) {
            this.cont.classList.add('markdown-editor__disabled')
        }
        this.buttons = this.cont.querySelectorAll(
            '[data-markdowneditor-button]',
        )

        // Selectors
        this.content = this.cont.querySelectorAll('.markdown-editor-content')
        this.buttonCode = this.cont.querySelectorAll(
            '.markdown-editor-button-code',
        )
        this.buttonPreview = this.cont.querySelectorAll(
            '.markdown-editor-button-preview',
        )
        this.buttonFullscreen = this.cont.querySelectorAll(
            '.markdown-editor-button-fullscreen',
        )
        this.buttonTranslateAssistant = this.cont.querySelectorAll(
            '.markdown-editor-button-translate-assistant-translate',
        )
        this.buttonTranslateAssistantRephrase = this.cont.querySelectorAll(
            '.markdown-editor-button-translate-assistant-rephrase',
        )

        // Store markdown index into datas
        this.setDataIndex(
            this.cont.querySelectorAll('.markdown-editor-button-code'),
        )
        this.setDataIndex(
            this.cont.querySelectorAll('.markdown-editor-button-preview'),
        )
        this.setDataIndex(
            this.cont.querySelectorAll('.markdown-editor-button-fullscreen'),
        )
        this.setDataIndex(this.cont.querySelectorAll('.markdown_textarea'))
        this.setDataIndex(this.buttonTranslateAssistant)
        this.setDataIndex(this.buttonTranslateAssistantRephrase)
        this.$editor.setAttribute('data-index', String(this.index))

        /*
         * Create preview tab.
         */
        const editorTabs = document.createElement('div')
        editorTabs.classList.add('markdown-editor-tabs')
        this.$editor.before(editorTabs)
        this.tabs = editorTabs

        const previewContainer = document.getElementById(
            'codemirror-preview-containers',
        )
        if (!previewContainer) {
            console.error('No preview container found')
            return
        }
        this.previewContainer = previewContainer

        const editorPreview = document.createElement('div')
        editorPreview.classList.add('markdown-editor-preview')
        this.$editor.after(editorPreview)
        this.preview = editorPreview

        this.tabs.append(this.$editor)
        this.previewContainer.append(this.preview)
        this.editor.refresh()

        this.fullscreenActive = false

        this.editor.on('change', this.onEditorChange)
        this.editor.on('focus', this.textareaFocus)
        this.editor.on('blur', this.textareaBlur)

        this.editor.on('drop', this.onDropFile)
        this.buttonPreview.forEach((button) => {
            button.addEventListener('click', this.buttonPreviewClick)
        })
        this.buttonTranslateAssistant.forEach((button) => {
            button.addEventListener('click', this.buttonTranslateAssistantClick)
        })
        this.buttonTranslateAssistantRephrase.forEach((button) => {
            button.addEventListener(
                'click',
                this.buttonTranslateAssistantRephraseClick,
            )
        })

        this.buttons.forEach((button) => {
            button.addEventListener('click', this.buttonClick)
        })

        window.requestAnimationFrame(() => {
            document.querySelectorAll('[data-uk-switcher]').forEach((el) => {
                el.addEventListener('show.uk.switcher', this.forceEditorUpdate)
            })
            this.forceEditorUpdate()
        })

        this.setAttribute('initialized', 'true')
    }

    get value(): string {
        if (!this.editor) return ''

        return this.editor.getValue()
    }

    set value(value: string) {
        if (!this.editor) return

        this.editor.setValue(value)
        this.forceEditorUpdate()
    }

    get strippedValue(): string {
        return stripTags(this.value)
    }

    setDataIndex(elements: NodeListOf<Element> | null) {
        if (!elements) return
        elements.forEach((element) => {
            element.setAttribute('data-index', String(this.index))
        })
    }

    async onDropFile(editor: Editor, event: DragEvent): Promise<void> {
        event.preventDefault()

        if (!event.dataTransfer?.files) return

        for (let i = 0; i < event.dataTransfer.files.length; i++) {
            window.dispatchEvent(new CustomEvent('requestLoaderShow'))
            const file = event.dataTransfer.files[i]
            const formData = new FormData()
            formData.append('_token', window.RozierConfig.ajaxToken || '')
            formData.append('form[attachment]', file)

            const response = await fetch(
                window.RozierConfig.routes?.documentsUploadPage || '',
                {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        // Required to prevent using this route as referer when login again
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                },
            )

            if (!response.ok) {
                const data = await response.json()
                if (data.errors) {
                    window.dispatchEvent(
                        new CustomEvent('pushToast', {
                            detail: {
                                message: data.message,
                                status: 'danger',
                            },
                        }),
                    )
                }
            } else {
                const data = await response.json()
                await window.Rozier.getMessages()
                this.onDropFileUploaded(editor, data)
            }
        }
    }

    onDropFileUploaded(editor: Editor, data: DocumentUploadResponse) {
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))

        if (data.success === true) {
            const mark =
                '![' + data.document.name + '](' + data.document.url + ')'
            editor.replaceSelection(mark)
        }
    }

    forceEditorUpdate() {
        this.editor.refresh()

        if (this.usePreview) {
            this.preview.innerHTML = this.markdownit.render(
                this.editor.getValue(),
            )
        }
    }

    buttonClick(event: Event) {
        if (this.editor.getOption('readOnly') === true) {
            return
        }
        const $button = event.currentTarget as HTMLElement
        const sel = this.editor.getSelections()

        if (sel.length > 0) {
            switch ($button.getAttribute('data-markdowneditor-button')) {
                case 'nbsp':
                    this.editor.replaceSelections(this.nbspSelections(sel))
                    break
                case 'nb-hyphen':
                    this.editor.replaceSelections(this.nbHyphenSelections(sel))
                    break
                case 'listUl':
                    this.editor.replaceSelections(this.listUlSelections(sel))
                    break
                case 'link':
                    this.editor.replaceSelections(this.linkSelections(sel))
                    break
                case 'image':
                    this.editor.replaceSelections(this.imageSelections(sel))
                    break
                case 'bold':
                    this.editor.replaceSelections(this.boldSelections(sel))
                    break
                case 'italic':
                    this.editor.replaceSelections(this.italicSelections(sel))
                    break
                case 'blockquote':
                    this.editor.replaceSelections(
                        this.blockquoteSelections(sel),
                    )
                    break
                case 'h1':
                    this.editor.replaceSelections(this.h1Selections(sel))
                    break
                case 'h2':
                    this.editor.replaceSelections(this.h2Selections(sel))
                    break
                case 'h3':
                    this.editor.replaceSelections(this.h3Selections(sel))
                    break
                case 'h4':
                    this.editor.replaceSelections(this.h4Selections(sel))
                    break
                case 'h5':
                    this.editor.replaceSelections(this.h5Selections(sel))
                    break
                case 'h6':
                    this.editor.replaceSelections(this.h6Selections(sel))
                    break
                case 'back':
                    this.editor.replaceSelections(this.backSelections(sel))
                    break
                case 'hr':
                    this.editor.replaceSelections(this.hrSelections(sel))
                    break
            }

            /*
             * Pos cursor after last selection
             */
            this.editor.focus()
        }
    }

    backSelections(selections: string[]): string[] {
        return selections.map(() => '   \n')
    }

    hrSelections(selections: string[]): string[] {
        return selections.map(() => '\n\n---\n\n')
    }

    nbspSelections(selections: string[]): string[] {
        return selections.map(() => ' ')
    }

    nbHyphenSelections(selections: string[]): string[] {
        return selections.map(() => '‑')
    }

    listUlSelections(selections: string[]): string[] {
        return selections.map((sel) => '\n\n* ' + sel + '\n\n')
    }

    linkSelections(selections: string[]): string[] {
        return selections.map((sel) => '[' + sel + '](http://)')
    }

    imageSelections(selections?: string[]): string[] {
        if (!selections) {
            selections = this.editor.getSelections()
        }
        return selections.map((sel) => '![' + sel + '](/files/)')
    }

    boldSelections(selections?: string[]): string[] {
        if (!selections) {
            selections = this.editor.getSelections()
        }
        return selections.map((sel) => '**' + sel + '**')
    }

    italicSelections(selections?: string[]): string[] {
        if (!selections) {
            selections = this.editor.getSelections()
        }
        return selections.map((sel) => '*' + sel + '*')
    }

    h1Selections(selections: string[]): string[] {
        return selections.map((sel) => '\n# ' + sel + '\n')
    }

    h2Selections(selections: string[]): string[] {
        return selections.map((sel) => '\n## ' + sel + '\n')
    }

    h3Selections(selections: string[]): string[] {
        return selections.map((sel) => '\n### ' + sel + '\n')
    }

    h4Selections(selections: string[]): string[] {
        return selections.map((sel) => '\n#### ' + sel + '\n')
    }

    h5Selections(selections: string[]): string[] {
        return selections.map((sel) => '\n##### ' + sel + '\n')
    }

    h6Selections(selections: string[]): string[] {
        return selections.map((sel) => '\n###### ' + sel + '\n')
    }

    blockquoteSelections(selections: string[]): string[] {
        return selections.map((sel) => '\n> ' + sel + '\n')
    }

    onEditorChange() {
        this.editor.save()

        if (this.usePreview) {
            if (this.refreshPreviewTimeout !== null) {
                window.cancelAnimationFrame(this.refreshPreviewTimeout)
            }
            this.refreshPreviewTimeout = window.requestAnimationFrame(() => {
                this.preview.innerHTML = this.markdownit.render(
                    this.editor.getValue(),
                )
            })
        }

        this.dispatchEvent(this.changeEvent)
    }

    textareaFocus() {
        this.cont?.classList.add('form-col-focus')
    }

    textareaBlur() {
        this.cont?.classList.remove('form-col-focus')
    }

    buttonPreviewClick(e: Event) {
        e.preventDefault()

        if (this.usePreview) {
            this.closePreview()
        } else {
            this.usePreview = true
            this.buttonPreview?.forEach((button) => {
                button.classList.add('active', 'rz-button--selected')
            })
            this.preview.classList.add('active')
            this.forceEditorUpdate()

            window.addEventListener('keyup', this.closePreview)

            document.body.dispatchEvent(
                new CustomEvent('markdownPreviewOpen', { detail: this }),
            )
        }
    }

    closePreview(e?: KeyboardEvent) {
        if (e) {
            if (e.keyCode === 27) {
                e.preventDefault()
            } else {
                return
            }
        }

        window.removeEventListener('keyup', this.closePreview)
        this.usePreview = false
        this.buttonPreview?.forEach((button) => {
            button.classList.remove('active', 'rz-button--selected')
        })
        this.preview.classList.remove('active')
    }

    destroy() {
        if (this.refreshPreviewTimeout !== null) {
            window.cancelAnimationFrame(this.refreshPreviewTimeout)
            this.refreshPreviewTimeout = null
        }

        this.editor?.off('change', this.onEditorChange)
        this.editor?.off('focus', this.textareaFocus)
        this.editor?.off('blur', this.textareaBlur)
        this.editor?.off('drop', this.onDropFile)

        this.buttonPreview?.forEach((button) => {
            button.removeEventListener('click', this.buttonPreviewClick)
        })
        this.buttonTranslateAssistant?.forEach((button) => {
            button.removeEventListener(
                'click',
                this.buttonTranslateAssistantClick,
            )
        })
        this.buttonTranslateAssistantRephrase?.forEach((button) => {
            button.removeEventListener(
                'click',
                this.buttonTranslateAssistantRephraseClick,
            )
        })
        this.buttons?.forEach((button) => {
            button.removeEventListener('click', this.buttonClick)
        })

        window.removeEventListener('keyup', this.closePreview)
        document.querySelectorAll('[data-uk-switcher]').forEach((el) => {
            el.removeEventListener('show.uk.switcher', this.forceEditorUpdate)
        })

        if (this.editor) {
            this.editor.toTextArea()
        }

        this.preview?.remove()
        this.tabs?.remove()
        this.cont?.classList.remove(
            'markdown-editor',
            'markdown-editor__disabled',
            'form-col-focus',
        )

        this.removeAttribute('initialized')
    }

    async buttonTranslateAssistantClick(e: Event): Promise<void> {
        e.preventDefault()

        const text = this.editor.getValue()
        const button = e.currentTarget as HTMLElement
        const targetLang = button.getAttribute('data-translate-locale')
        const translatePath = button.getAttribute('data-translate-path')

        if (!translatePath || !text || text.length === 0) {
            return
        }

        const response = await fetch(translatePath, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: new URLSearchParams({
                text: text,
                targetLang: targetLang || '',
            }),
        })

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`)
        }

        const result: TranslationResponse = await response.json()
        this.editor.setValue(result.translatedText)
    }

    async buttonTranslateAssistantRephraseClick(e: Event): Promise<void> {
        e.preventDefault()

        const text = this.editor.getValue()
        const button = e.currentTarget as HTMLElement
        const targetLang = button.getAttribute('data-translate-locale')
        const rephrasePath = button.getAttribute('data-translate-path')

        if (!rephrasePath || !text || text.length === 0) {
            return
        }

        const response = await fetch(rephrasePath, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: new URLSearchParams({
                text: text,
                targetLang: targetLang || '',
            }),
        })

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`)
        }

        const result: TranslationResponse = await response.json()
        this.editor.setValue(result.translatedText)
    }
}
