import Lazyload from './Lazyload'
import VueApp from './App'
import { fadeIn, fadeOut } from './utils/animation'
import { sleep } from './utils/sleep'

/**
 * Rozier root entry
 */
export default class Rozier {
    constructor() {
        this.windowWidth = null
        this.windowHeight = null
        this.resizeFirst = true

        this.mainTrees = null
        this.canvasLoader = null

        this.backTopBtn = null
        this.maintreeElementNameRightClick =
            this.maintreeElementNameRightClick.bind(this)

        this.resize = this.resize.bind(this)
    }

    onDocumentReady() {
        this.initLoader()
        this.lazyload = new Lazyload()
        this.vueApp = new VueApp()

        // --- Selectors --- //
        this.mainTrees = document.querySelector('#main-trees')

        window.addEventListener('resize', this.resize)
        this.resize()

        this.lazyload.generalBind()
        this.bindMainTreeLangSwitcher()

        /*
         * Fetch main tree widgets for the first time
         */
        this.refreshAsideMainTree()

        window.addEventListener('pageshowend', () => {
            this.refreshAsideMainTree()
        })

        window.addEventListener('requestAllNodeTreeChange', () => {
            this.refreshAllNodeTrees()
            this.getMessages()
        })
        window.addEventListener('requestLoaderShow', () => {
            this.canvasLoader.show()
        })
        window.addEventListener('requestLoaderHide', () => {
            this.canvasLoader.hide()
        })
        window.addEventListener('pushToast', (event) => {
            if (!event.detail || !event.detail.message) {
                return
            }
            window.UIkit.notify({
                message: event.detail.message,
                status: event.detail.status || 'success',
                timeout: 2000,
                pos: 'bottom-right',
            })
        })
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

    /**
     * Bind main trees
     */
    bindMainTrees() {
        // Contextual menu on tree element names
        const mainTreeElementName =
            this.mainTrees?.querySelectorAll('.tree-element-name') || []
        mainTreeElementName.forEach((element) => {
            element.removeEventListener(
                'contextmenu',
                this.maintreeElementNameRightClick,
            )
            element.addEventListener(
                'contextmenu',
                this.maintreeElementNameRightClick,
            )
        })
    }

    /**
     * Main tree element name right click.
     * @return {boolean}
     */
    maintreeElementNameRightClick(e) {
        e.preventDefault()

        // Close all other contextual menus
        document
            .querySelectorAll('.tree-contextualmenu')
            .forEach((contextualMenu) => {
                if (contextualMenu.classList.contains('uk-open')) {
                    contextualMenu.classList.remove('uk-open')
                }
            })

        const contextualMenu = e.currentTarget.parentElement.querySelector(
            '.tree-contextualmenu',
        )
        if (contextualMenu) {
            if (!contextualMenu.classList.contains('uk-open')) {
                contextualMenu.classList.add('uk-open')
            } else {
                contextualMenu.classList.remove('uk-open')
            }
        }

        return false
    }

    bindMainTreeLangSwitcher() {
        this.bindLangButtonClicked('.node-tree-langs', (translationId) => {
            this.refreshMainNodeTree(translationId)
        })

        this.bindLangButtonClicked('.folder-tree-langs', (translationId) => {
            this.refreshMainFolderTree(translationId)
        })

        this.bindLangButtonClicked('.tag-tree-langs', (translationId) => {
            this.refreshMainTagTree(translationId)
        })
    }

    /**
     * Bind main node tree langs.
     *
     * @return {boolean}
     */
    bindLangButtonClicked(wrapperClass, callBack) {
        document.body.addEventListener('click', (event) => {
            const target = event.target.closest(
                `.rz-aside ${wrapperClass} button`,
            )
            if (target) {
                window.dispatchEvent(new CustomEvent('requestLoaderShow'))
                const translationId = parseInt(
                    target.getAttribute('data-translation-id'),
                    10,
                )
                callBack(translationId)
                return false
            }
        })
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
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        })
        const data = await response.json()
        if (!data.messages) {
            return []
        }
        return data.messages
    }

    /**
     * Get messages.
     */
    async getMessages() {
        const messages = await this.fetchSessionMessages()
        if (
            messages.confirm &&
            Array.isArray(messages.confirm) &&
            messages.confirm.length > 0
        ) {
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
        if (
            messages.error &&
            Array.isArray(messages.error) &&
            messages.error.length > 0
        ) {
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

    /**
     * @param translationId
     */
    refreshAllNodeTrees(translationId) {
        const promises = []
        promises.push(this.refreshMainNodeTree(translationId))

        /*
         * Stack trees
         */
        if (this.lazyload.stackNodeTrees.treeAvailable()) {
            promises.push(this.lazyload.stackNodeTrees.refreshNodeTree())
        }

        /*
         * Children node fields widgets;
         */
        if (this.lazyload.childrenNodesFields.treeAvailable()) {
            this.lazyload.childrenNodesFields.nodeTrees.forEach((nodeTree) => {
                promises.push(
                    this.lazyload.childrenNodesFields.refreshNodeTree(nodeTree),
                )
            })
        }
        return Promise.all(promises)
    }

    async refreshAsideTreeContent(treeHTML = '') {
        if (!treeHTML) {
            console.warn('No treeHTML provided to refreshAsideTreeContent')
            return
        }

        const treeContainer = document.querySelector('#tree-container')
        const previousElement = treeContainer.querySelector('.rz-tree-wrapper')

        const temporaryElement = document.createElement('div')
        temporaryElement.innerHTML = treeHTML
        const newElement = temporaryElement.querySelector('.rz-tree-wrapper')

        await fadeOut(treeContainer)

        if (newElement) {
            treeContainer.appendChild(newElement)
        }
        if (previousElement) {
            previousElement.remove()
        }

        await fadeIn(treeContainer)

        this.bindMainTrees()
        this.resize()
        this.lazyload.bindAjaxLink()
    }

    async refreshMainTree(baseUrl, queryOptions = {}) {
        const currentRootTree = document.querySelector(
            '#tree-container .rz-tree-wrapper',
        )

        if (currentRootTree && !queryOptions?.translationId) {
            queryOptions.translationId = currentRootTree.getAttribute(
                'data-translation-id',
            )
        }

        const query = new URLSearchParams({
            _token: window.RozierConfig.ajaxToken,
            _action: 'requestMainTree',
            url: window.location.pathname,
            ...queryOptions,
        })

        // Default api Route detect which tree depending on url query
        let fetchUrl = baseUrl || window.RozierConfig.routes.treeAjaxGateway
        const treeResponse = await fetch(`${fetchUrl}?${query.toString()}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        if (!treeResponse.ok) {
            throw treeResponse
        }

        const data = await treeResponse.json()

        // It will be better to have a common key in response for all possible trees types
        const treeHTML =
            data?.['nodeTree'] ||
            data?.['tagTree'] ||
            data?.['folderTree'] ||
            data?.['tree']

        if (data && typeof treeHTML !== 'undefined') {
            const wrapper = document.querySelector('#tree-container')

            const treeTypeId =
                `${data.tree_type || 'node'}-tree` +
                (queryOptions?.translationId
                    ? `-${queryOptions?.translationId}`
                    : '-main-locale')

            // Same tree, no refresh needed
            if (treeTypeId === wrapper.getAttribute('data-tree-id')) {
                return
            }

            this.refreshAsideTreeContent(treeHTML)
            wrapper.setAttribute('data-tree-id', treeTypeId)
        }
    }

    async refreshAsideMainTree(baseUrl = null, query = {}) {
        try {
            await this.refreshMainTree(baseUrl, query)
        } catch {
            console.debug('[Rozier.refreshAsideMainTree] Retrying in 3 seconds')
            // Wait for background jobs to be done
            await sleep(3000)
            await this.refreshMainTree(baseUrl, query)
        }

        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    async refreshMainNodeTree(translationId = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes.nodesTreeAjax,
            {
                translationId: translationId,
            },
        )
    }

    async refreshMainTagTree(translationId = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes.tagsTreeAjax,
            {
                translationId: translationId,
            },
        )
    }

    async refreshMainFolderTree(translationId = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes.foldersTreeAjax,
            {
                translationId: translationId,
            },
        )
    }

    resize() {
        this.windowWidth = window.offsetWidth
        this.windowHeight = window.offsetHeight

        // Set resize first to false
        if (this.resizeFirst) this.resizeFirst = false
    }
}
