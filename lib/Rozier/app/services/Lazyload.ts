import StackNodeTree from '~/widgets/StackNodeTree'
import SettingsSaveButtons from '~/widgets/SettingsSaveButtons'
import NodeStatuses from '~/widgets/NodeStatuses'

type Unbindable = { unbind?: () => void } | null

export default class Lazyload {
    linksSelector: HTMLAnchorElement[]
    currentRequest: AbortController | null
    currentTimeout: number | null
    settingsSaveButtons: Unbindable
    stackNodeTrees: StackNodeTree | null
    nodeStatuses: NodeStatuses | null

    constructor() {
        this.linksSelector = []
        this.currentRequest = null
        this.currentTimeout = null
        this.settingsSaveButtons = null
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
        const url = window.location.href
        this.linksSelector.forEach((link) => {
            if (link.href === url) {
                link.setAttribute('aria-current', 'page')
            } else {
                link.removeAttribute('aria-current')
            }
            link.classList.add('rz-ajax-link')
            link.removeEventListener('click', this.onClick)
            link.addEventListener('click', this.onClick)
        })
    }

    /**
     * General bind on page load
     */
    generalBind() {
        this.generalUnbind([this.stackNodeTrees, this.settingsSaveButtons])
        this.bindAjaxLink()

        this.stackNodeTrees = new StackNodeTree()

        this.settingsSaveButtons = new SettingsSaveButtons()

        window.Rozier?.bindMainTrees()
        window.Rozier!.nodeStatuses = new NodeStatuses()
        window.Rozier?.getMessages()
    }

    generalUnbind(objects: Unbindable[]) {
        for (const object of objects) {
            if (object?.unbind) {
                object.unbind()
            }
        }
    }

    /**
     * Resize
     */
    resize() {}
}
