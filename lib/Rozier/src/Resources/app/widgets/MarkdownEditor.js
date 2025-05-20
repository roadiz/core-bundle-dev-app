import { addClass, stripTags } from '../utils/plugins'
import { Expo, TweenLite } from 'gsap'
import markdownit from 'markdown-it'
import markdownItFootnote from 'markdown-it-footnote'

/**
 * Markdown Editor
 */
export default class MarkdownEditor {
    /**
     * @param {HTMLTextAreaElement} textarea
     * @param index
     */
    constructor(textarea, index) {
        this.markdownit = new markdownit()
        this.markdownit.use(markdownItFootnote)

        this.textarea = textarea
        this.usePreview = false

        this.editor = window.CodeMirror.fromTextArea(this.textarea, {
            mode: 'gfm',
            lineNumbers: false,
            tabSize: 4,
            styleActiveLine: true,
            indentWithTabs: false,
            lineWrapping: true,
            viewportMargin: Infinity,
            enterMode: 'keep',
            direction: this.textarea.hasAttribute('dir') && this.textarea.getAttribute('dir') === 'rtl' ? 'rtl' : 'ltr',
            readOnly: this.textarea.hasAttribute('disabled') && this.textarea.getAttribute('disabled') === 'disabled',
        })

        this.editor.addKeyMap({
            'Ctrl-B': (cm) => {
                cm.replaceSelections(this.boldSelections())
            },
            'Ctrl-I': (cm) => {
                cm.replaceSelections(this.italicSelections())
            },
            'Cmd-B': (cm) => {
                cm.replaceSelections(this.boldSelections())
            },
            'Cmd-I': (cm) => {
                cm.replaceSelections(this.italicSelections())
            },
        })

        // Selectors
        this.cont = this.textarea.closest('.uk-form-row')
        this.parentForm = this.textarea.closest('form')
        this.index = index
        this.buttonCode = null
        this.buttonPreview = null
        this.buttonFullscreen = null
        this.count = null
        this.countCurrent = null
        this.limit = 0
        this.countMinLimit = 0
        this.countMaxLimit = 0
        this.countMaxLimitText = null
        this.countAlertActive = false
        this.fullscreenActive = false

        this.closePreview = this.closePreview.bind(this)
        this.textareaChange = this.textareaChange.bind(this)
        this.textareaFocus = this.textareaFocus.bind(this)
        this.textareaBlur = this.textareaBlur.bind(this)
        this.onDropFile = this.onDropFile.bind(this)
        this.buttonPreviewClick = this.buttonPreviewClick.bind(this)
        this.buttonClick = this.buttonClick.bind(this)
        this.forceEditorUpdate = this.forceEditorUpdate.bind(this)

        // Methods
        this.init()
    }

    /**
     * Init
     * @return {[type]} [description]
     */
    init() {
        this.editor.on('change', this.textareaChange)

        if (this.cont && this.textarea) {
            this.$editor = this.cont.querySelector('.CodeMirror')

            this.cont.classList.add('markdown-editor')
            if (this.editor.getOption('readOnly') === true) {
                this.cont.classList.add('markdown-editor__disabled')
            }
            this.buttons = this.cont.querySelectorAll('[data-markdowneditor-button]')
            // Selectors
            this.content = this.cont.querySelectorAll('.markdown-editor-content')
            this.buttonCode = this.cont.querySelectorAll('.markdown-editor-button-code')
            this.buttonPreview = this.cont.querySelectorAll('.markdown-editor-button-preview')
            this.buttonFullscreen = this.cont.querySelectorAll('.markdown-editor-button-fullscreen')
            this.count = this.cont.querySelectorAll('.markdown-editor-count')
            this.countCurrent = this.cont.querySelectorAll('.count-current')
            this.countMaxLimitText = this.cont.querySelectorAll('.count-limit')

            // Store markdown index into datas
            const buttonsCode = this.cont.querySelectorAll('.markdown-editor-button-code')
            buttonsCode.forEach((button) => {
                button.setAttribute('data-index', this.index)
            })
            const buttonPreview = this.cont.querySelectorAll('.markdown-editor-button-preview')
            buttonPreview.forEach((button) => {
                button.setAttribute('data-index', this.index)
            })
            const buttonFullscreen = this.cont.querySelectorAll('.markdown-editor-button-fullscreen')
            buttonFullscreen.forEach((button) => {
                button.setAttribute('data-index', this.index)
            })
            const mdTextarea = this.cont.querySelectorAll('.markdown_textarea')
            mdTextarea.forEach((textarea) => {
                textarea.setAttribute('data-index', this.index)
            })
            this.$editor.setAttribute('data-index', this.index)

            /*
             * Create preview tab.
             */
            const editorTabs = document.createElement('div')
            editorTabs.classList.add('markdown-editor-tabs')
            this.$editor.before(editorTabs)
            this.tabs = editorTabs
            this.previewContainer = document.getElementById('codemirror-preview-containers')

            const editorPreview = document.createElement('div')
            editorPreview.classList.add('markdown-editor-preview')
            this.$editor.after(editorPreview)
            this.preview = editorPreview

            this.tabs.append(this.$editor)
            this.previewContainer.append(this.preview)
            this.editor.refresh()

            // Check if a max length is defined
            if (this.textarea.hasAttribute('data-max-length') && this.textarea.getAttribute('data-max-length') !== '') {
                this.limit = true
                this.countMaxLimit = parseInt(this.textarea.getAttribute('data-max-length'))

                if (this.countCurrent.length && this.countMaxLimitText.length && this.count.length) {
                    this.countCurrent[0].innerHTML = stripTags(this.editor.getValue()).length
                    this.countMaxLimitText[0].innerHTML = this.textarea.getAttribute('data-max-length')
                    this.count[0].style.display = 'block'
                }
            }

            if (this.textarea.hasAttribute('data-min-length') && this.textarea.getAttribute('data-min-length') !== '') {
                this.limit = true
                this.countMinLimit = parseInt(this.textarea.getAttribute('data-min-length'))
            }

            if (
                this.textarea.hasAttribute('data-max-length') &&
                this.textarea.hasAttribute('data-min-length') &&
                this.textarea.getAttribute('data-min-length') === '' &&
                this.textarea.getAttribute('data-max-length') === ''
            ) {
                this.limit = false
                this.countMaxLimit = null
                this.countAlertActive = null
            }

            this.fullscreenActive = false

            if (this.limit) {
                // Check if current length is over limit
                if (stripTags(this.editor.getValue()).length > this.countMaxLimit) {
                    this.countAlertActive = true
                    addClass(this.cont, 'content-limit')
                } else if (stripTags(this.editor.getValue()).length < this.countMinLimit) {
                    this.countAlertActive = true
                    addClass(this.cont, 'content-limit')
                } else this.countAlertActive = false
            }

            this.editor.on('change', this.textareaChange)
            this.editor.on('focus', this.textareaFocus)
            this.editor.on('blur', this.textareaBlur)

            this.editor.on('drop', this.onDropFile)
            this.buttonPreview.forEach((button) => {
                button.addEventListener('click', this.buttonPreviewClick)
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
        }
    }

    async onDropFile(editor, event) {
        event.preventDefault(event)

        for (let i = 0; i < event.dataTransfer.files.length; i++) {
            window.Rozier.lazyload.canvasLoader.show()
            let file = event.dataTransfer.files[i]
            let formData = new FormData()
            formData.append('_token', window.RozierConfig.ajaxToken)
            formData.append('form[attachment]', file)

            const response = await fetch(window.RozierConfig.routes.documentsUploadPage, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: formData,
            })
            if (!response.ok) {
                const data = await response.json()
                if (data.errors) {
                    window.UIkit.notify({
                        message: data.message,
                        status: 'danger',
                        timeout: 2000,
                        pos: 'top-center',
                    })
                }
            } else {
                const data = await response.json()
                await window.Rozier.getMessages()
                this.onDropFileUploaded(editor, data)
            }
        }
    }

    onDropFileUploaded(editor, data) {
        window.Rozier.lazyload.canvasLoader.hide()

        if (data.success === true) {
            let mark = '![' + data.document.name + '](' + data.document.url + ')'

            editor.replaceSelection(mark)
        }
    }

    forceEditorUpdate() {
        this.editor.refresh()

        if (this.usePreview) {
            this.preview.innerHTML = this.markdownit.render(this.editor.getValue())
        }
    }

    /**
     * @param {Event} event
     */
    buttonClick(event) {
        if (this.editor.getOption('readOnly') === true) {
            return false
        }
        let $button = event.currentTarget
        let sel = this.editor.getSelections()

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
                    this.editor.replaceSelections(this.blockquoteSelections(sel))
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

    backSelections(selections) {
        for (let i in selections) {
            selections[i] = '   \n'
        }
        return selections
    }

    hrSelections(selections) {
        for (let i in selections) {
            selections[i] = '\n\n---\n\n'
        }
        return selections
    }

    nbspSelections(selections) {
        for (let i in selections) {
            selections[i] = ' '
        }
        return selections
    }

    nbHyphenSelections(selections) {
        for (let i in selections) {
            selections[i] = '‑'
        }
        return selections
    }

    listUlSelections(selections) {
        for (let i in selections) {
            selections[i] = '\n\n* ' + selections[i] + '\n\n'
        }
        return selections
    }

    linkSelections(selections) {
        for (let i in selections) {
            selections[i] = '[' + selections[i] + '](http://)'
        }
        return selections
    }

    imageSelections(selections) {
        if (!selections) {
            selections = this.editor.getSelections()
        }
        for (let i in selections) {
            selections[i] = '![' + selections[i] + '](/files/)'
        }
        return selections
    }

    boldSelections(selections) {
        if (!selections) {
            selections = this.editor.getSelections()
        }

        for (let i in selections) {
            selections[i] = '**' + selections[i] + '**'
        }

        return selections
    }

    italicSelections(selections) {
        if (!selections) {
            selections = this.editor.getSelections()
        }

        for (let i in selections) {
            selections[i] = '*' + selections[i] + '*'
        }

        return selections
    }

    h2Selections(selections) {
        for (let i in selections) {
            selections[i] = '\n## ' + selections[i] + '\n'
        }

        return selections
    }

    h3Selections(selections) {
        for (let i in selections) {
            selections[i] = '\n### ' + selections[i] + '\n'
        }

        return selections
    }

    h4Selections(selections) {
        for (let i in selections) {
            selections[i] = '\n#### ' + selections[i] + '\n'
        }

        return selections
    }

    h5Selections(selections) {
        for (let i in selections) {
            selections[i] = '\n##### ' + selections[i] + '\n'
        }

        return selections
    }

    h6Selections(selections) {
        for (let i in selections) {
            selections[i] = '\n###### ' + selections[i] + '\n'
        }

        return selections
    }

    blockquoteSelections(selections) {
        for (let i in selections) {
            selections[i] = '\n> ' + selections[i] + '\n'
        }

        return selections
    }

    /**
     * Textarea change
     */
    textareaChange() {
        this.editor.save()

        if (this.usePreview) {
            window.cancelAnimationFrame(this.refreshPreviewTimeout)
            this.refreshPreviewTimeout = window.requestAnimationFrame(() => {
                this.preview.innerHTML = this.markdownit.render(this.editor.getValue())
            })
        }

        if (this.limit) {
            window.requestAnimationFrame(() => {
                let textareaVal = this.editor.getValue()
                let textareaValStripped = stripTags(textareaVal)
                let textareaValLength = textareaValStripped.length

                this.countCurrent[0].innerHTML = textareaValLength

                if (textareaValLength > this.countMaxLimit) {
                    if (!this.countAlertActive) {
                        this.cont.classList.add('content-limit')
                        this.countAlertActive = true
                    }
                } else if (textareaValLength < this.countMinLimit) {
                    if (!this.countAlertActive) {
                        this.cont.classList.add('content-limit')
                        this.countAlertActive = true
                    }
                } else {
                    if (this.countAlertActive) {
                        this.cont.classList.remove('content-limit')
                        this.countAlertActive = false
                    }
                }
            })
        }
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

    /**
     * Button preview click
     */
    buttonPreviewClick(e) {
        e.preventDefault()

        let width = this.preview.offsetWidth

        if (this.usePreview) {
            this.closePreview()
        } else {
            this.usePreview = true
            this.buttonPreview.forEach((button) => {
                button.classList.add('uk-active', 'active')
            })
            this.preview.classList.add('active')
            this.forceEditorUpdate()
            TweenLite.fromTo(this.preview, 1, { x: width * -1, opacity: 0 }, { x: 0, ease: Expo.easeOut, opacity: 1 })
            window.addEventListener('keyup', this.closePreview)

            let openPreview = new CustomEvent('markdownPreviewOpen', {
                detail: this,
            })

            document.body.dispatchEvent(openPreview)
        }
    }

    /**
     *
     */
    closePreview(e) {
        if (e) {
            if (e.keyCode === 27) {
                e.preventDefault()
            } else {
                return
            }
        }

        let width = this.preview.offsetWidth
        window.removeEventListener('keyup', this.closePreview)
        this.usePreview = false
        this.buttonPreview.forEach((button) => {
            button.classList.remove('uk-active', 'active')
        })
        TweenLite.fromTo(
            this.preview,
            1,
            { x: 0, opacity: 1 },
            {
                x: width * -1,
                opacity: 0,
                ease: Expo.easeOut,
                onComplete: () => {
                    this.preview.classList.remove('active')
                },
            }
        )
    }

    destroy() {
        this.preview.remove()
    }

    /**
     * Window resize callback
     * @return {[type]} [description]
     */
    resize() {}
}
