import DocumentUploader from './components/documents/DocumentUploader'
import AttributeValuePosition from './components/attribute-values/AttributeValuePosition'
import CustomFormFieldsPosition from './components/custom-form-fields/CustomFormFieldsPosition'
import InputLengthWatcher from './widgets/InputLengthWatcher'
import ChildrenNodesField from './widgets/ChildrenNodesField'
import StackNodeTree from './widgets/StackNodeTree'
import SettingsSaveButtons from './widgets/SettingsSaveButtons'
import NodeStatuses from './widgets/NodeStatuses'
import YamlEditor from './widgets/YamlEditor'
import MarkdownEditor from './widgets/MarkdownEditor'
import JsonEditor from './widgets/JsonEditor'
import CssEditor from './widgets/CssEditor'
import LeafletGeotagField from './widgets/LeafletGeotagField'
import MultiLeafletGeotagField from './widgets/MultiLeafletGeotagField'

export default class Lazyload {
    constructor() {
        this.linksSelector = null
        this.currentRequest = null
        this.inputLengthWatcher = null
        this.documentUploader = null
        this.childrenNodesFields = null
        this.geotagField = null
        this.multiGeotagField = null
        this.tagAutocomplete = null
        this.attributeValuesPosition = null
        this.customFormFieldsPosition = null
        this.settingsSaveButtons = null
        this.markdownEditors = []
        this.jsonEditors = []
        this.cssEditors = []
        this.yamlEditors = []

        // Bind methods
        this.onPopState = this.onPopState.bind(this)
        this.onClick = this.onClick.bind(this)

        this.parseLinks()

        window.removeEventListener('popstate', this.onPopState)
        window.addEventListener('popstate', this.onPopState)

        /*
         * Start history with first hard loaded page
         */
        window.history.pushState({}, document.title, window.location.href)
    }

    parseLinks() {
        this.linksSelector = Array.from(
            document.querySelectorAll(
                'a:not([target=_blank]):not([download]):not([href="#"])',
            ),
        ).filter((link) => {
            const href = link.getAttribute('href')
            return (
                typeof href !== 'undefined' &&
                href !== null &&
                !link.classList.contains('rz-no-ajax-link') &&
                href !== '' &&
                href !== '#' &&
                (href.indexOf(window.RozierConfig.baseUrl) >= 0 ||
                    href.charAt(0) === '/' ||
                    href.charAt(0) === '?')
            )
        })
    }

    /**
     * Bind links to load pages
     * @param {MouseEvent} event
     */
    onClick(event) {
        const link = event.currentTarget
        const href = link.getAttribute('href')

        event.preventDefault()

        window.requestAnimationFrame(() => {
            window.history.pushState({}, null, href)
            this.onPopState(null)
        })

        return false
    }

    /**
     * On pop state
     * @param {Event} event
     */
    onPopState(event) {
        let state = null

        if (event !== null && event.originalEvent) {
            state = event.originalEvent.state
        }

        if (typeof state === 'undefined' || state === null) {
            state = window.history.state
        }

        if (state !== null) {
            window.dispatchEvent(new CustomEvent('requestLoaderShow'))
            this.loadContent(state, window.location)
        }
    }

    /**
     * Load content (ajax)
     * @param {Object} state
     * @param {Object} location
     */
    loadContent(state, location) {
        /*
         * Delay loading if user is click like devil
         */
        if (this.currentTimeout) {
            window.cancelAnimationFrame(this.currentTimeout)
        }

        this.currentTimeout = window.requestAnimationFrame(async () => {
            /*
             * Trigger event on window to notify open
             * widgets to close.
             */
            let pageChangeEvent = new CustomEvent('pagechange')
            window.dispatchEvent(pageChangeEvent)

            try {
                let url = ''
                const path = location.href.split('?')[0]
                const params = new URLSearchParams(location.href.split('?')[1])
                if (state.headerData) {
                    /**
                     * @param {string} key
                     * @param {string|number|Array<string|number>} value
                     */
                    for (let [key, value] of Object.entries(state.headerData)) {
                        if (Array.isArray(value)) {
                            value.forEach((v, i) => {
                                params.append(key + '[' + i + ']', v)
                            })
                        } else {
                            params.set(key, value)
                        }
                    }
                }
                if (params.toString() !== '') {
                    url = path + '?' + params.toString()
                } else {
                    url = path
                }

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        Accept: 'text/html',
                    },
                })
                if (!response.ok) {
                    throw response
                }
                const data = await response.text()
                this.applyContent(data)
                let pageLoadEvent = new CustomEvent('pageload', {
                    detail: data,
                })
                window.dispatchEvent(pageLoadEvent)
            } catch (response) {
                const data = await response.text()
                if (data) {
                    try {
                        let exception = JSON.parse(data)
                        window.dispatchEvent(
                            new CustomEvent('pushToast', {
                                detail: {
                                    message: exception.message,
                                    status: 'danger',
                                },
                            }),
                        )
                    } catch {
                        // No valid JsonResponse, need to refresh page
                        window.location.href = location.href
                    }
                } else {
                    window.dispatchEvent(
                        new CustomEvent('pushToast', {
                            detail: {
                                message:
                                    window.RozierConfig.messages.forbiddenPage,
                                status: 'danger',
                            },
                        }),
                    )
                }
            }
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        })
    }

    refreshCodemirrorEditor() {
        for (let editor of this.markdownEditors) {
            editor.forceEditorUpdate()
        }
        for (let editor of this.yamlEditors) {
            editor.forceEditorUpdate()
        }
        for (let editor of this.cssEditors) {
            editor.forceEditorUpdate()
        }
        for (let editor of this.jsonEditors) {
            editor.forceEditorUpdate()
        }
    }

    destroyCodemirrorEditor() {
        for (let editor of this.markdownEditors) {
            editor.destroy()
        }
        for (let editor of this.yamlEditors) {
            editor.destroy()
        }
        for (let editor of this.cssEditors) {
            editor.destroy()
        }
        for (let editor of this.jsonEditors) {
            editor.destroy()
        }
    }

    /**
     * Apply content to main content.
     *
     * @param {string} data
     * @return {void}
     */
    async applyContent(data) {
        const container = document.querySelector('[data-ajax-root]')
        if (!container) {
            console.error('No [data-ajax-root] found in the document.')
            return
        }

        const tempData = document.createElement('div')
        tempData.setHTMLUnsafe(data)

        /*
         * If AJAX request data is an entire HTML page.
         */
        /** @var {HTMLElement} ajaxRoot */
        const ajaxRoot = tempData.querySelector('[data-ajax-root]')
        if (ajaxRoot) {
            container.setHTMLUnsafe(ajaxRoot.innerHTML)
        } else {
            container.setHTMLUnsafe(tempData.innerHTML)
        }

        const metaTitle = tempData.querySelectorAll('title, meta[name="title"]')
        if (metaTitle.length) {
            document.title =
                metaTitle[0].getAttribute('content') || metaTitle[0].innerText
        }

        const pageShowEndEvent = new CustomEvent('pageshowend')
        window.dispatchEvent(pageShowEndEvent)

        this.generalBind()

        // TODO: Need to scroll to top
    }

    bindAjaxLink() {
        this.parseLinks()

        this.linksSelector.forEach((link) => {
            link.classList.add('rz-ajax-link')
            // Remove existing listener from this specific link before adding a new one
            link.removeEventListener('click', this.onClick)
            // Add the listener to this specific link
            link.addEventListener('click', this.onClick)
        })
    }

    /**
     * General bind on page load
     * @return {[type]} [description]
     */
    generalBind() {
        this.generalUnbind([
            this.inputLengthWatcher,
            this.documentUploader,
            this.childrenNodesFields,
            this.geotagField,
            this.multiGeotagField,
            this.stackNodeTrees,
            this.tagAutocomplete,
            this.attributeValuesPosition,
            this.customFormFieldsPosition,
            this.settingsSaveButtons,
        ])
        this.bindAjaxLink()
        this.markdownEditors = []
        this.jsonEditors = []
        this.cssEditors = []
        this.yamlEditors = []

        this.inputLengthWatcher = new InputLengthWatcher()
        this.documentUploader = new DocumentUploader(
            window.RozierConfig.messages.dropzone,
        )
        this.childrenNodesFields = new ChildrenNodesField()
        this.geotagField = new LeafletGeotagField()
        this.multiGeotagField = new MultiLeafletGeotagField()

        this.stackNodeTrees = new StackNodeTree()

        this.attributeValuesPosition = new AttributeValuePosition()
        this.customFormFieldsPosition = new CustomFormFieldsPosition()
        this.settingsSaveButtons = new SettingsSaveButtons()

        // Codemirror
        this.initMarkdownEditors()
        this.initJsonEditors()
        this.initCssEditors()
        this.initYamlEditors()
        this.initFilterBars()
        this.initCollectionsForms()

        window.Rozier.initNestables()
        window.Rozier.bindMainTrees()
        window.Rozier.nodeStatuses = new NodeStatuses()

        window.Rozier.getMessages()
    }

    generalUnbind(objects) {
        this.destroyCodemirrorEditor()
        for (let object of objects) {
            if (object) {
                object.unbind()
            }
        }
    }

    initCollectionsForms(scope = null) {
        let types = null
        if (scope !== null) {
            types = scope.querySelectorAll('.rz-collection-form-type')
        } else {
            types = document.querySelectorAll('.rz-collection-form-type')
        }
        if (types.length) {
            // Jquery collection
            $(types).collection({
                up: '<a tabindex="-1" class="uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-angle-up"></i></a>',
                down: '<a tabindex="-1" class="uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-angle-down"></i></a>',
                add: '<a tabindex="-1" class="uk-button-primary uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-plus"></i></a>',
                remove: '<a tabindex="-1" class="uk-button-danger uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-minus"></i></a>',
                /**
                 * @param collection
                 * @param {jQuery} element
                 * @return {boolean}
                 */
                after_add: (collection, element) => {
                    const el = element[0]
                    this.initMarkdownEditors(el)
                    this.initJsonEditors(el)
                    this.initCssEditors(el)
                    this.initYamlEditors(el)
                    this.initCollectionsForms(el)

                    let vueComponents = el.querySelectorAll('[data-vuejs]')
                    // Create each component
                    vueComponents.forEach((el) => {
                        window.Rozier.vueApp.mainContentComponents.push(
                            window.Rozier.vueApp.buildComponent(el),
                        )
                    })
                    return true
                },
            })
        }
    }

    /**
     * @param {HTMLElement|undefined} scope
     */
    initMarkdownEditors(scope) {
        // Init markdown-preview
        let textareasMarkdown = []
        if (scope) {
            textareasMarkdown = scope.querySelectorAll(
                'textarea[data-rz-markdowneditor]',
            )
        } else {
            textareasMarkdown = document.querySelectorAll(
                'textarea[data-rz-markdowneditor]',
            )
        }
        let editorCount = textareasMarkdown.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.markdownEditors.push(
                    new MarkdownEditor(textareasMarkdown[i], i),
                )
            }
        }
    }

    /**
     * @param {HTMLElement|undefined} scope
     */
    initJsonEditors(scope) {
        // Init json-preview
        let textareasJson = []
        if (scope) {
            textareasJson = scope.querySelectorAll(
                'textarea[data-rz-jsoneditor]',
            )
        } else {
            textareasJson = document.querySelectorAll(
                'textarea[data-rz-jsoneditor]',
            )
        }
        let editorCount = textareasJson.length
        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.jsonEditors.push(new JsonEditor(textareasJson[i], i))
            }
        }
    }

    /**
     * @param {HTMLElement|undefined} scope
     */
    initCssEditors(scope) {
        // Init css-preview
        let textareasCss = []
        if (scope) {
            textareasCss = scope.querySelectorAll('textarea[data-rz-csseditor]')
        } else {
            textareasCss = document.querySelectorAll(
                'textarea[data-rz-csseditor]',
            )
        }
        let editorCount = textareasCss.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.cssEditors.push(new CssEditor(textareasCss[i], i))
            }
        }
    }

    /**
     * @param {HTMLElement|undefined} scope
     */
    initYamlEditors(scope) {
        // Init yaml-preview
        let textareasYaml = []
        if (scope) {
            textareasYaml = scope.querySelectorAll(
                'textarea[data-rz-yamleditor]',
            )
        } else {
            textareasYaml = document.querySelectorAll(
                'textarea[data-rz-yamleditor]',
            )
        }
        let editorCount = textareasYaml.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.yamlEditors.push(new YamlEditor(textareasYaml[i], i))
            }
        }
    }

    initFilterBars() {
        const selectItemPerPage = document.querySelectorAll(
            'select.item-per-page',
        )
        if (selectItemPerPage.length) {
            const handleChange = (event) => {
                const form = event.currentTarget.closest('form')
                if (form) {
                    form.requestSubmit()
                }
            }
            selectItemPerPage.forEach((selectElement) => {
                selectElement.addEventListener('change', handleChange)
            })
        }
    }

    /**
     * Resize
     */
    resize() {}
}
