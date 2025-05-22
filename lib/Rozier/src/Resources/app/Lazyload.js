import {Expo, TweenLite} from 'gsap'
import DocumentUploader from './components/documents/DocumentUploader'
import AttributeValuePosition from './components/attribute-values/AttributeValuePosition'
import CustomFormFieldsPosition from './components/custom-form-fields/CustomFormFieldsPosition'
import NodeTreeContextActions from './components/trees/NodeTreeContextActions'
import NodeEditSource from './components/node/NodeEditSource'
import InputLengthWatcher from './widgets/InputLengthWatcher'
import ChildrenNodesField from './widgets/ChildrenNodesField'
import StackNodeTree from './widgets/StackNodeTree'
import SaveButtons from './widgets/SaveButtons'
import TagAutocomplete from './widgets/TagAutocomplete'
import FolderAutocomplete from './widgets/FolderAutocomplete'
import SettingsSaveButtons from './widgets/SettingsSaveButtons'
import NodeTree from './widgets/NodeTree'
import NodeStatuses from './widgets/NodeStatuses'
import YamlEditor from './widgets/YamlEditor'
import MarkdownEditor from './widgets/MarkdownEditor'
import JsonEditor from './widgets/JsonEditor'
import CssEditor from './widgets/CssEditor'
import LeafletGeotagField from './widgets/LeafletGeotagField'
import MultiLeafletGeotagField from './widgets/MultiLeafletGeotagField'
import TagEdit from './components/tag/TagEdit'
import MainTreeTabs from './components/tabs/MainTreeTabs'
import {fadeIn, fadeOut} from "./utils/animation";

export default class Lazyload {
    constructor() {
        this.linksSelector = null
        this.canvasLoaderContainer = null
        this.mainColor = null
        this.currentRequest = null
        this.nodeTreeContextActions = null
        this.inputLengthWatcher = null
        this.documentUploader = null
        this.childrenNodesFields = null
        this.geotagField = null
        this.multiGeotagField = null
        this.saveButtons = null
        this.tagAutocomplete = null
        this.folderAutocomplete = null
        this.attributeValuesPosition = null
        this.customFormFieldsPosition = null
        this.settingsSaveButtons = null
        this.nodeEditSource = null
        this.tagEdit = null
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

        this.canvasLoaderContainer = document.getElementById('canvasloader-container')
        this.mainColor = window.getComputedStyle(document.documentElement).getPropertyValue('--rz-accent-color');
        this.initLoader()

        /*
         * Start history with first hard loaded page
         */
        window.history.pushState({}, document.title, window.location.href)
    }

    /**
     * Init loader
     */
    initLoader() {
        this.canvasLoader = new window.CanvasLoader('canvasloader-container')
        this.canvasLoader.setColor(this.mainColor)
        this.canvasLoader.setShape('square')
        this.canvasLoader.setDensity(90)
        this.canvasLoader.setRange(0.8)
        this.canvasLoader.setSpeed(4)
        this.canvasLoader.setFPS(30)
    }

    parseLinks() {
        this.linksSelector = document.querySelectorAll('a:not([target=_blank]):not([download]):not([href="#"])')
    }

    /**
     * Bind links to load pages
     * @param {Event} event
     */
    onClick(event) {
        const link = event.currentTarget;
        const href = link.getAttribute('href');

        if (
            typeof href !== 'undefined' &&
            href !== null &&
            !link.classList.contains('rz-no-ajax-link') &&
            href !== '' &&
            href !== '#' &&
            (href.indexOf(window.RozierConfig.baseUrl) >= 0 || href.charAt(0) === '/' || href.charAt(0) === '?')
        ) {
            event.preventDefault()

            window.requestAnimationFrame(() => {
                window.history.pushState({}, null, href);
                this.onPopState(null)
            })

            return false
        }
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
            this.canvasLoader.show()
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
                        'X-Partial': true,
                        Accept: 'text/html',
                    },
                })
                if (!response.ok) {
                    throw response
                }
                const data = await response.text()
                this.applyContent(data)
                let pageLoadEvent = new CustomEvent('pageload', { detail: data })
                window.dispatchEvent(pageLoadEvent)
            } catch (response) {
                const data = await response.text()
                if (data) {
                    try {
                        let exception = JSON.parse(data)
                        window.UIkit.notify({
                            message: exception.message,
                            status: 'danger',
                            timeout: 3000,
                            pos: 'top-center',
                        })
                    } catch (e) {
                        // No valid JsonResponse, need to refresh page
                        window.location.href = location.href
                    }
                } else {
                    window.UIkit.notify({
                        message: window.RozierConfig.messages.forbiddenPage,
                        status: 'danger',
                        timeout: 3000,
                        pos: 'top-center',
                    })
                }
            }
            this.canvasLoader.hide()
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
        let container = document.getElementById('main-content-scrollable')
        let old = container.querySelector('.content-global')

        let tempData = document.createElement('div');
        tempData.innerHTML = data;

        /*
         * If AJAX request data is an entire HTML page.
         */
        /** @var {HTMLElement} ajaxRoot */
        const ajaxRoot = tempData.querySelector('[data-ajax-root]');
        if (ajaxRoot) {
            tempData = document.createElement('div');
            tempData.innerHTML = ajaxRoot.innerHTML;
        }

        tempData.classList.add('new-content-global')
        // Removed previous ajax meta[title] tags
        const metaTitleToRemove = container.querySelectorAll('meta[name="title"]')
        metaTitleToRemove.forEach((meta) => {
            meta.remove()
        })
        // Append Ajax loaded data to DOM
        container.append(tempData)

        const metaTitle = container.querySelectorAll('meta[name="title"]')
        if (metaTitle.length) {
            document.title = metaTitle[0].getAttribute('content')
        }

        tempData = container.querySelector('.new-content-global')

        if (old && tempData) {
            await fadeOut(old, 100)
            old.remove()
            this.generalBind()

            await fadeIn(tempData, 200)
            tempData.classList.remove('new-content-global')
            const pageShowEndEvent = new CustomEvent('pageshowend')
            window.dispatchEvent(pageShowEndEvent)
        }
    }

    bindAjaxLink() {
        this.parseLinks()

        this.linksSelector.forEach(link => {
            // Remove existing listener from this specific link before adding a new one
            link.removeEventListener('click', this.onClick);
            // Add the listener to this specific link
            link.addEventListener('click', this.onClick);
        });
    }

    /**
     * General bind on page load
     * @return {[type]} [description]
     */
    generalBind() {
        this.generalUnbind([
            this.mainTreeTabs,
            this.inputLengthWatcher,
            this.documentUploader,
            this.childrenNodesFields,
            this.geotagField,
            this.multiGeotagField,
            this.stackNodeTrees,
            this.nodeTreeContextActions,
            this.tagAutocomplete,
            this.folderAutocomplete,
            this.attributeValuesPosition,
            this.customFormFieldsPosition,
            this.settingsSaveButtons,
            this.nodeEditSource,
            this.tagEdit,
            this.nodeTree,
        ])
        this.bindAjaxLink()
        this.markdownEditors = []
        this.jsonEditors = []
        this.cssEditors = []
        this.yamlEditors = []

        this.mainTreeTabs = new MainTreeTabs()
        this.inputLengthWatcher = new InputLengthWatcher()
        this.documentUploader = new DocumentUploader(window.RozierConfig.messages.dropzone)
        this.childrenNodesFields = new ChildrenNodesField()
        this.geotagField = new LeafletGeotagField()
        this.multiGeotagField = new MultiLeafletGeotagField()

        this.stackNodeTrees = new StackNodeTree()

        if (this.saveButtons) {
            this.saveButtons.unbind()
        }
        this.saveButtons = new SaveButtons()

        this.tagAutocomplete = new TagAutocomplete()
        this.folderAutocomplete = new FolderAutocomplete()
        this.attributeValuesPosition = new AttributeValuePosition()
        this.customFormFieldsPosition = new CustomFormFieldsPosition()
        this.nodeTreeContextActions = new NodeTreeContextActions()
        this.settingsSaveButtons = new SettingsSaveButtons()
        this.nodeEditSource = new NodeEditSource()
        this.tagEdit = new TagEdit()
        this.nodeTree = new NodeTree()

        // Codemirror
        this.initMarkdownEditors()
        this.initJsonEditors()
        this.initCssEditors()
        this.initYamlEditors()
        this.initFilterBars()
        this.initCollectionsForms()

        // Animate actions menu
        if (document.querySelectorAll('.actions-menu').length) {
            TweenLite.to('.actions-menu', 0.5, { right: 0, delay: 0.4, ease: Expo.easeOut })
        }

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
        const _this = this
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
                after_add: (collection, element) => {
                    _this.initMarkdownEditors(element)
                    _this.initJsonEditors(element)
                    _this.initCssEditors(element)
                    _this.initYamlEditors(element)
                    _this.initCollectionsForms(element)

                    let vueComponents = element.querySelectorAll('[data-vuejs]')
                    // Create each component
                    vueComponents.forEach((el) => {
                        window.Rozier.vueApp.mainContentComponents.push(window.Rozier.vueApp.buildComponent(el))
                    })
                    return true
                },
            })
        }
    }

    initMarkdownEditors(scope) {
        // Init markdown-preview
        let textareasMarkdown = []
        if (scope && scope.length) {
            textareasMarkdown = scope.querySelectorAll('textarea[data-rz-markdowneditor]')
        } else {
            textareasMarkdown = document.querySelectorAll('textarea[data-rz-markdowneditor]')
        }
        let editorCount = textareasMarkdown.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.markdownEditors.push(new MarkdownEditor(textareasMarkdown[i], i))
            }
        }
    }

    initJsonEditors(scope) {
        // Init json-preview
        let textareasJson = []
        if (scope && scope.length) {
            textareasJson = scope.querySelectorAll('textarea[data-rz-jsoneditor]')
        } else {
            textareasJson = document.querySelectorAll('textarea[data-rz-jsoneditor]')
        }
        let editorCount = textareasJson.length
        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.jsonEditors.push(new JsonEditor(textareasJson[i], i))
            }
        }
    }

    initCssEditors(scope) {
        // Init css-preview
        let textareasCss = []
        if (scope && scope.length) {
            textareasCss = scope.querySelectorAll('textarea[data-rz-csseditor]')
        } else {
            textareasCss = document.querySelectorAll('textarea[data-rz-csseditor]')
        }
        let editorCount = textareasCss.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.cssEditors.push(new CssEditor(textareasCss[i], i))
            }
        }
    }

    initYamlEditors(scope) {
        // Init yaml-preview
        let textareasYaml = []
        if (scope && scope.length) {
            textareasYaml = scope.querySelectorAll('textarea[data-rz-yamleditor]')
        } else {
            textareasYaml = document.querySelectorAll('textarea[data-rz-yamleditor]')
        }
        let editorCount = textareasYaml.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.yamlEditors.push(new YamlEditor(textareasYaml[i], i))
            }
        }
    }

    initFilterBars() {
        const selectItemPerPage = document.querySelectorAll('select.item-per-page')
        if (selectItemPerPage.length) {
            const handleChange = (event) => {
                const form = event.currentTarget.closest('form');
                if (form) {
                    form.requestSubmit();
                }
            };
            selectItemPerPage.forEach(selectElement => {
                selectElement.addEventListener('change', handleChange);
            });
        }
    }

    /**
     * Resize
     */
    resize() {
        if (this.canvasLoaderContainer) {
            this.canvasLoaderContainer.style.left =
                window.Rozier.mainContentScrollableOffsetLeft + window.Rozier.mainContentScrollableWidth / 2 + 'px'
        }
    }
}
