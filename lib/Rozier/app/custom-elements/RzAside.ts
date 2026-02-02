import RoadizElement from '~/utils/custom-element/RoadizElement'
import { fadeIn, fadeOut } from '~/utils/animation'
import { sleep } from '~/utils/sleep'

export default class RzAside extends RoadizElement {
    private onPageShowEnd: () => void
    private onAllNodeTreeChange: () => void
    private onMainTreeRefresh: () => void
    private onBindMainTreesRequest: () => void
    private onAjaxLinkBindRequest: () => void
    private onMessagesRefresh: () => void
    private onLangButtonClick: (event: Event) => void
    private onMainTreeContextMenu: (event: Event) => void

    constructor() {
        super()

        this.onPageShowEnd = this.handlePageShowEnd.bind(this)
        this.onAllNodeTreeChange = this.handleAllNodeTreeChange.bind(this)
        this.onMainTreeRefresh = this.handleMainTreeRefresh.bind(this)
        this.onBindMainTreesRequest = this.handleBindMainTreesRequest.bind(this)
        this.onAjaxLinkBindRequest = this.handleAjaxLinkBindRequest.bind(this)
        this.onMessagesRefresh = this.handleMessagesRefresh.bind(this)
        this.onLangButtonClick = this.handleLangButtonClick.bind(this)
        this.onMainTreeContextMenu =
            this.maintreeElementNameRightClick.bind(this)
    }

    private get rozier() {
        return (window as Window & { Rozier?: any }).Rozier
    }

    connectedCallback() {
        this.listen(this, 'click', this.onLangButtonClick)
        this.refreshAsideMainTree()

        window.addEventListener('pageshowend', this.onPageShowEnd)
        window.addEventListener(
            'requestAllNodeTreeChange',
            this.onAllNodeTreeChange,
        )
        window.addEventListener(
            'requestAllNodeTreeRefresh',
            this.onAllNodeTreeChange,
        )
        window.addEventListener(
            'requestMainTreeRefresh',
            this.onMainTreeRefresh,
        )
        window.addEventListener(
            'requestBindMainTrees',
            this.onBindMainTreesRequest,
        )
        window.addEventListener(
            'requestAjaxLinkBind',
            this.onAjaxLinkBindRequest,
        )
        window.addEventListener(
            'requestMessagesRefresh',
            this.onMessagesRefresh,
        )
    }

    disconnectedCallback() {
        super.disconnectedCallback()

        window.removeEventListener('pageshowend', this.onPageShowEnd)
        window.removeEventListener(
            'requestAllNodeTreeChange',
            this.onAllNodeTreeChange,
        )
        window.removeEventListener(
            'requestAllNodeTreeRefresh',
            this.onAllNodeTreeChange,
        )
        window.removeEventListener(
            'requestMainTreeRefresh',
            this.onMainTreeRefresh,
        )
        window.removeEventListener(
            'requestBindMainTrees',
            this.onBindMainTreesRequest,
        )
        window.removeEventListener(
            'requestAjaxLinkBind',
            this.onAjaxLinkBindRequest,
        )
        window.removeEventListener(
            'requestMessagesRefresh',
            this.onMessagesRefresh,
        )
    }

    private handlePageShowEnd() {
        this.refreshAsideMainTree()
    }

    private handleAllNodeTreeChange() {
        this.refreshAllNodeTrees()
        window.dispatchEvent(new CustomEvent('requestMessagesRefresh'))
    }

    private handleMainTreeRefresh() {
        this.refreshAsideMainTree()
    }

    private handleMessagesRefresh() {
        this.rozier?.getMessages?.()
    }

    private handleBindMainTreesRequest() {
        this.bindMainTrees()
    }

    private handleAjaxLinkBindRequest() {
        this.rozier?.lazyload?.bindAjaxLink?.()
    }

    private get treeContainer() {
        return this.querySelector('.rz-aside__body') as HTMLElement | null
    }

    private handleLangButtonClick(event: Event) {
        const target = (event.target as HTMLElement | null)?.closest('button')
        if (!target || !this.contains(target)) {
            return
        }

        const translationIdRaw = target.getAttribute('data-translation-id')
        const translationId = translationIdRaw
            ? parseInt(translationIdRaw, 10)
            : undefined

        if (target.closest('[data-tree-type="node"]')) {
            window.dispatchEvent(new CustomEvent('requestLoaderShow'))
            this.refreshMainNodeTree(translationId)
            return
        }

        if (target.closest('[data-tree-type="folder"]')) {
            window.dispatchEvent(new CustomEvent('requestLoaderShow'))
            this.refreshMainFolderTree(translationId)
            return
        }

        if (target.closest('[data-tree-type="tag"]')) {
            window.dispatchEvent(new CustomEvent('requestLoaderShow'))
            this.refreshMainTagTree(translationId)
        }
    }

    /**
     * Bind main trees
     */
    bindMainTrees() {
        const treeElements = this.querySelectorAll('.tree-element-name')
        treeElements.forEach((element) => {
            element.removeEventListener(
                'contextmenu',
                this.onMainTreeContextMenu,
            )
            element.addEventListener('contextmenu', this.onMainTreeContextMenu)
        })
    }

    /**
     * Main tree element name right click.
     * @return {boolean}
     */
    maintreeElementNameRightClick(event: Event) {
        event.preventDefault()

        document
            .querySelectorAll('.tree-contextualmenu')
            .forEach((contextualMenu) => {
                if (contextualMenu.classList.contains('uk-open')) {
                    contextualMenu.classList.remove('uk-open')
                }
            })

        const target = event.currentTarget as HTMLElement | null
        const contextualMenu = target?.parentElement?.querySelector(
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

    /**
     * @param translationId
     */
    refreshAllNodeTrees(translationId?: number) {
        const promises: Array<Promise<unknown>> = []
        promises.push(this.refreshMainNodeTree(translationId))

        if (this.rozier?.lazyload?.stackNodeTrees?.treeAvailable?.()) {
            promises.push(this.rozier.lazyload.stackNodeTrees.refreshNodeTree())
        }

        return Promise.all(promises)
    }

    async refreshAsideTreeContent(treeHTML = '') {
        if (!treeHTML) {
            console.warn('No treeHTML provided to refreshAsideTreeContent')
            return
        }

        const treeContainer = this.treeContainer
        if (!treeContainer) {
            console.warn('No tree container found to refreshAsideTreeContent')
            return
        }

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
        this.rozier?.resize?.()
        this.rozier?.lazyload?.bindAjaxLink?.()
    }

    async refreshMainTree(
        baseUrl?: string | null,
        queryOptions: Record<string, string | number> = {},
    ) {
        const treeContainer = this.treeContainer
        if (!treeContainer) {
            return
        }

        const currentRootTree = treeContainer.querySelector('.rz-tree-wrapper')

        if (currentRootTree && !queryOptions?.translationId) {
            const translationId = currentRootTree.getAttribute(
                'data-translation-id',
            )
            if (translationId) {
                queryOptions.translationId = translationId
            }
        }

        const query = new URLSearchParams({
            _token: window.RozierConfig.ajaxToken,
            _action: 'requestMainTree',
            url: window.location.pathname,
            ...queryOptions,
        })

        const fetchUrl =
            baseUrl ||
            (window.RozierConfig.routes as { treeAjaxGateway?: string })
                ?.treeAjaxGateway
        if (!fetchUrl) {
            return
        }
        const treeResponse = await fetch(`${fetchUrl}?${query.toString()}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        if (!treeResponse.ok) {
            throw treeResponse
        }

        const data = await treeResponse.json()

        const treeHTML =
            data?.['nodeTree'] ||
            data?.['tagTree'] ||
            data?.['folderTree'] ||
            data?.['tree']

        if (data && typeof treeHTML !== 'undefined') {
            const treeTypeId =
                `${data.tree_type || 'node'}-tree` +
                (queryOptions?.translationId
                    ? `-${queryOptions?.translationId}`
                    : '-main-locale')

            if (treeTypeId === treeContainer.getAttribute('data-tree-id')) {
                return
            }

            this.refreshAsideTreeContent(treeHTML)
            treeContainer.setAttribute('data-tree-id', treeTypeId)
        }
    }

    async refreshAsideMainTree(
        baseUrl: string | null = null,
        query: Record<string, string | number> = {},
    ) {
        try {
            await this.refreshMainTree(baseUrl, query)
        } catch {
            console.debug(
                '[RzAside.refreshAsideMainTree] Retrying in 3 seconds',
            )
            await sleep(3000)
            await this.refreshMainTree(baseUrl, query)
        }

        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    async refreshMainNodeTree(translationId: number | undefined = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes?.nodesTreeAjax || null,
            { translationId },
        )
    }

    async refreshMainTagTree(translationId: number | undefined = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes?.tagsTreeAjax || null,
            { translationId },
        )
    }

    async refreshMainFolderTree(translationId: number | undefined = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes?.foldersTreeAjax || null,
            { translationId },
        )
    }
}
