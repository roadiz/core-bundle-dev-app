import Lazyload from '~/Lazyload'
import VueApp from '~/App'

/**
 * Rozier root entry
 */
export default class Rozier {
    windowWidth: number | null
    windowHeight: number | null
    resizeFirst: boolean
    canvasLoader: CanvasLoader | null
    lazyload: Lazyload | null = null
    vueApp: VueApp | null = null

    constructor() {
        this.windowWidth = null
        this.windowHeight = null
        this.resizeFirst = true

        this.canvasLoader = null

        this.resize = this.resize.bind(this)
    }

    onDocumentReady() {
        this.initLoader()
        this.lazyload = new Lazyload()
        this.vueApp = new VueApp()

        window.addEventListener('resize', this.resize)
        this.resize()

        this.lazyload.generalBind()
        window.addEventListener('requestLoaderShow', () => {
            this.canvasLoader?.show()
        })
        window.addEventListener('requestLoaderHide', () => {
            this.canvasLoader?.hide()
        })
    }

    getAsideElement() {
        return document.querySelector('rz-aside') as RzAsideElement | null
    }

    bindMainTrees() {
        this.getAsideElement()?.bindMainTrees?.()
    }

    refreshMainNodeTree(translationId?: number) {
        return this.getAsideElement()?.refreshMainNodeTree?.(translationId)
    }

    refreshMainTagTree(translationId?: number) {
        return this.getAsideElement()?.refreshMainTagTree?.(translationId)
    }

    refreshMainFolderTree(translationId?: number) {
        return this.getAsideElement()?.refreshMainFolderTree?.(translationId)
    }

    /**
     * Init loader
     */
    initLoader() {
        this.canvasLoader = new window.CanvasLoader('canvasloader-container')
        this.canvasLoader.setColor(
            window
                .getComputedStyle(document.documentElement)
                .getPropertyValue('--rz-accent-color'),
        )
        this.canvasLoader.setShape('square')
        this.canvasLoader.setDensity(90)
        this.canvasLoader.setRange(0.8)
        this.canvasLoader.setSpeed(4)
        this.canvasLoader.setFPS(30)
    }

    async fetchSessionMessages() {
        const query = new URLSearchParams({
            _csrf_token: window.RozierConfig.ajaxToken,
        })
        const url =
            window.RozierConfig.routes.ajaxSessionMessages +
            '?' +
            query.toString()
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        })
        const data = (await response.json()) as { messages?: unknown }
        if (!data.messages) {
            return []
        }
        return data.messages
    }

    /**
     * Get messages.
     */
    async getMessages() {
        const messages = (await this.fetchSessionMessages()) as {
            confirm?: string[]
            error?: string[]
        }
        if (messages.confirm && messages.confirm.length > 0) {
            messages.confirm.forEach((message) => {
                window.dispatchEvent(
                    new CustomEvent('pushToast', {
                        detail: {
                            message: message,
                            status: 'success',
                        },
                    }),
                )
            })
        }
        if (messages.error && messages.error.length > 0) {
            messages.error.forEach((message) => {
                window.dispatchEvent(
                    new CustomEvent('pushToast', {
                        detail: {
                            message: message,
                            status: 'danger',
                        },
                    }),
                )
            })
        }
    }

    resize() {
        this.windowWidth = window.innerWidth
        this.windowHeight = window.innerHeight

        if (this.resizeFirst) this.resizeFirst = false
    }
}
