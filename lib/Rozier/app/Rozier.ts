import Lazyload from '~/Lazyload'
import VueApp from '~/App'
import { dispatchSessionToast } from '~/session-message'
import type RzAside from './custom-elements/RzAside'

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
    nodeStatuses = null

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
        window.addEventListener('requestMessagesRefresh', () => {
            this.getMessages()
        })
    }

    getAsideElement() {
        return document.querySelector('rz-aside') as RzAside | null
    }

    bindMainTrees() {
        // this.getAsideElement()?.bindMainTrees?.()
    }

    refreshMainNodeTree(translationId?: string) {
        return this.getAsideElement()?.refreshMainNodeTree?.(translationId)
    }

    refreshMainTagTree(translationId?: string) {
        return this.getAsideElement()?.refreshMainTagTree?.(translationId)
    }

    refreshMainFolderTree(translationId?: string) {
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

    async getMessages() {
        await dispatchSessionToast()
    }

    resize() {
        this.windowWidth = window.innerWidth
        this.windowHeight = window.innerHeight

        if (this.resizeFirst) this.resizeFirst = false
    }
}
