import CustomFormFieldsPosition from '~/components/custom-form-fields/CustomFormFieldsPosition'
import StackNodeTree from '~/widgets/StackNodeTree'
import SettingsSaveButtons from '~/widgets/SettingsSaveButtons'
import NodeStatuses from '~/widgets/NodeStatuses'
import YamlEditor from '~/widgets/YamlEditor'
import JsonEditor from '~/widgets/JsonEditor'
import CssEditor from '~/widgets/CssEditor'

type Unbindable = { unbind?: () => void } | null

declare const $: {
    (elements: NodeListOf<Element> | Element[]): {
        collection(options: Record<string, unknown>): void
    }
}

type JQuery = ArrayLike<HTMLElement> & {
    0: HTMLElement
}

export default class Lazyload {
    linksSelector: HTMLAnchorElement[]
    currentRequest: AbortController | null
    currentTimeout: number | null
    inputLengthWatcher: Unbindable
    documentUploader: Unbindable
    geotagField: Unbindable
    multiGeotagField: Unbindable
    tagAutocomplete: Unbindable
    customFormFieldsPosition: Unbindable
    settingsSaveButtons: Unbindable
    jsonEditors: JsonEditor[]
    cssEditors: CssEditor[]
    yamlEditors: YamlEditor[]
    stackNodeTrees: StackNodeTree | null
    nodeStatuses: NodeStatuses | null

    constructor() {
        this.linksSelector = []
        this.currentRequest = null
        this.currentTimeout = null
        this.inputLengthWatcher = null
        this.documentUploader = null
        this.geotagField = null
        this.multiGeotagField = null
        this.tagAutocomplete = null
        this.customFormFieldsPosition = null
        this.settingsSaveButtons = null
        this.jsonEditors = []
        this.cssEditors = []
        this.yamlEditors = []
        this.stackNodeTrees = null
        this.nodeStatuses = null

        this.onPopState = this.onPopState.bind(this)
        this.onClick = this.onClick.bind(this)

        this.parseLinks()

        window.removeEventListener('popstate', this.onPopState)
        window.addEventListener('popstate', this.onPopState)

        window.history.pushState({}, document.title, window.location.href)
    }

    parseLinks() {
        this.linksSelector = Array.from(
            document.querySelectorAll<HTMLAnchorElement>(
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
     */
    onClick(event: MouseEvent) {
        const link = event.currentTarget as HTMLAnchorElement | null
        const href = link?.getAttribute('href')
        if (!href) return false

        event.preventDefault()

        window.requestAnimationFrame(() => {
            window.history.pushState({}, null, href)
            this.onPopState(null)
        })

        return false
    }

    /**
     * On pop state
     */
    onPopState(event: PopStateEvent | null) {
        let state: Record<string, unknown> | null = null

        if (event !== null && 'state' in event) {
            state = event.state as Record<string, unknown> | null
        }

        if (typeof state === 'undefined' || state === null) {
            state = window.history.state as Record<string, unknown> | null
        }

        if (state !== null) {
            window.dispatchEvent(new CustomEvent('requestLoaderShow'))
            this.loadContent(state, window.location)
        }
    }

    /**
     * Load content (ajax)
     */
    loadContent(state: Record<string, unknown>, location: Location) {
        if (this.currentTimeout) {
            window.cancelAnimationFrame(this.currentTimeout)
        }

        this.currentTimeout = window.requestAnimationFrame(async () => {
            window.dispatchEvent(new CustomEvent('pagechange'))

            try {
                let url = ''
                const path = location.href.split('?')[0]
                const params = new URLSearchParams(location.href.split('?')[1])
                const headerData = state.headerData as
                    | Record<string, string | number | Array<string | number>>
                    | undefined

                if (headerData) {
                    for (const [key, value] of Object.entries(headerData)) {
                        if (Array.isArray(value)) {
                            value.forEach((v, i) => {
                                params.append(`${key}[${i}]`, String(v))
                            })
                        } else {
                            params.set(key, String(value))
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
                await this.applyContent(data)
                window.dispatchEvent(
                    new CustomEvent('pageload', {
                        detail: data,
                    }),
                )
            } catch (response) {
                const errorResponse = response as Response
                const data = await errorResponse.text()
                if (data) {
                    try {
                        const exception = JSON.parse(data) as {
                            message?: string
                        }
                        window.dispatchEvent(
                            new CustomEvent('pushToast', {
                                detail: {
                                    message: exception.message,
                                    status: 'danger',
                                },
                            }),
                        )
                    } catch {
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
        for (const editor of this.yamlEditors) {
            editor.forceEditorUpdate()
        }
        for (const editor of this.cssEditors) {
            editor.forceEditorUpdate()
        }
        for (const editor of this.jsonEditors) {
            editor.forceEditorUpdate()
        }
    }

    destroyCodemirrorEditor() {
        for (const editor of this.yamlEditors) {
            editor.destroy()
        }
        for (const editor of this.cssEditors) {
            editor.destroy()
        }
        for (const editor of this.jsonEditors) {
            editor.destroy()
        }
    }

    /**
     * Apply content to main content.
     */
    async applyContent(data: string) {
        const container = document.querySelector('[data-ajax-root]')
        if (!container) {
            console.error('No [data-ajax-root] found in the document.')
            return
        }

        const tempData = document.createElement('div')
        tempData.setHTMLUnsafe(data)

        const ajaxRoot = tempData.querySelector('[data-ajax-root]')
        if (ajaxRoot) {
            container.setHTMLUnsafe(ajaxRoot.innerHTML)
        } else {
            container.setHTMLUnsafe(tempData.innerHTML)
        }

        const metaTitle = tempData.querySelectorAll('title, meta[name="title"]')
        if (metaTitle.length) {
            const titleElement = metaTitle[0] as HTMLElement
            document.title =
                titleElement.getAttribute('content') ||
                titleElement.textContent ||
                ''
        }

        window.dispatchEvent(new CustomEvent('pageshowend'))

        this.generalBind()
    }

    bindAjaxLink() {
        this.parseLinks()

        this.linksSelector.forEach((link) => {
            link.classList.add('rz-ajax-link')
            link.removeEventListener('click', this.onClick)
            link.addEventListener('click', this.onClick)
        })
    }

    /**
     * General bind on page load
     */
    generalBind() {
        this.generalUnbind([
            this.inputLengthWatcher,
            this.geotagField,
            this.multiGeotagField,
            this.stackNodeTrees,
            this.tagAutocomplete,
            this.customFormFieldsPosition,
            this.settingsSaveButtons,
        ])
        this.bindAjaxLink()
        this.jsonEditors = []
        this.cssEditors = []
        this.yamlEditors = []

        this.stackNodeTrees = new StackNodeTree()

        this.customFormFieldsPosition = new CustomFormFieldsPosition()
        this.settingsSaveButtons = new SettingsSaveButtons()

        this.initJsonEditors()
        this.initCssEditors()
        this.initYamlEditors()

        window.Rozier?.bindMainTrees()
        window.Rozier!.nodeStatuses = new NodeStatuses()
        window.Rozier?.getMessages()
    }

    generalUnbind(objects: Unbindable[]) {
        this.destroyCodemirrorEditor()
        for (const object of objects) {
            if (object?.unbind) {
                object.unbind()
            }
        }
    }

    initCollectionsForms(scope?: HTMLElement | null) {
        let types: NodeListOf<Element> | null = null
        if (scope !== null && typeof scope !== 'undefined') {
            types = scope.querySelectorAll('.rz-collection-form-type')
        } else {
            types = document.querySelectorAll('.rz-collection-form-type')
        }
        if (types.length) {
            $(types).collection({
                up: '<a tabindex="-1" class="uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-angle-up"></i></a>',
                down: '<a tabindex="-1" class="uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-angle-down"></i></a>',
                add: '<a tabindex="-1" class="uk-button-primary uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-plus"></i></a>',
                remove: '<a tabindex="-1" class="uk-button-danger uk-button uk-button-small" href="#"><i tabindex="-1" class="uk-icon uk-icon-minus"></i></a>',
                after_add: (_collection: unknown, element: JQuery) => {
                    const el = element[0] as HTMLElement
                    this.initJsonEditors(el)
                    this.initCssEditors(el)
                    this.initYamlEditors(el)
                    this.initCollectionsForms(el)

                    const vueComponents = el.querySelectorAll('[data-vuejs]')
                    vueComponents.forEach((vueEl) => {
                        if (!window.Rozier) return
                        window.Rozier.vueApp.mainContentComponents.push(
                            window.Rozier.vueApp.buildComponent(vueEl),
                        )
                    })
                    return true
                },
            })
        }
    }

    initJsonEditors(scope?: HTMLElement) {
        let textareasJson: NodeListOf<HTMLTextAreaElement>
        if (scope) {
            textareasJson = scope.querySelectorAll(
                'textarea[data-rz-jsoneditor]',
            )
        } else {
            textareasJson = document.querySelectorAll(
                'textarea[data-rz-jsoneditor]',
            )
        }
        const editorCount = textareasJson.length
        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.jsonEditors.push(new JsonEditor(textareasJson[i], i))
            }
        }
    }

    initCssEditors(scope?: HTMLElement) {
        let textareasCss: NodeListOf<HTMLTextAreaElement>
        if (scope) {
            textareasCss = scope.querySelectorAll('textarea[data-rz-csseditor]')
        } else {
            textareasCss = document.querySelectorAll(
                'textarea[data-rz-csseditor]',
            )
        }
        const editorCount = textareasCss.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.cssEditors.push(new CssEditor(textareasCss[i], i))
            }
        }
    }

    initYamlEditors(scope?: HTMLElement) {
        let textareasYaml: NodeListOf<HTMLTextAreaElement>
        if (scope) {
            textareasYaml = scope.querySelectorAll(
                'textarea[data-rz-yamleditor]',
            )
        } else {
            textareasYaml = document.querySelectorAll(
                'textarea[data-rz-yamleditor]',
            )
        }
        const editorCount = textareasYaml.length

        if (editorCount) {
            for (let i = 0; i < editorCount; i++) {
                this.yamlEditors.push(new YamlEditor(textareasYaml[i], i))
            }
        }
    }

    /**
     * Resize
     */
    resize() {}
}
