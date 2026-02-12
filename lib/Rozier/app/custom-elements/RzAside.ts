import RoadizElement from '~/utils/custom-element/RoadizElement'
import { fadeIn, fadeOut } from '~/utils/animation'
import { sleep } from '~/utils/sleep'

export default class RzAside extends RoadizElement {
    private onLangButtonClick: (event: Event) => void
    private currentTranslationId: number | null = null

    constructor() {
        super()

        this.onPageShowEnd = this.onPageShowEnd.bind(this)
        this.onLangButtonClick = this.handleLangButtonClick.bind(this)
        this.handleMessagesRefresh = this.handleMessagesRefresh.bind(this)
    }

    private get rozier() {
        return window.Rozier
    }

    connectedCallback() {
        this.listen(this, 'click', this.onLangButtonClick)
        this.refreshAsideMainTree()
        window.addEventListener('pageshowend', this.onPageShowEnd)
        window.addEventListener(
            'requestMessagesRefresh',
            this.handleMessagesRefresh,
        )
    }

    disconnectedCallback() {
        super.disconnectedCallback()

        window.removeEventListener('pageshowend', this.onPageShowEnd)
        window.removeEventListener(
            'requestMessagesRefresh',
            this.handleMessagesRefresh,
        )
    }

    private onPageShowEnd() {
        this.refreshAsideMainTree()
    }

    private handleMessagesRefresh() {
        this.rozier?.getMessages?.()
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
    async refreshTreeContent(treeHTML = '') {
        if (!treeHTML) {
            console.warn('No treeHTML provided to refreshTreeContent')
            return
        }

        const treeContainer = this.treeContainer
        if (!treeContainer) {
            console.warn('No tree container found to refreshTreeContent')
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
            await this.refreshTreeContent(treeHTML)

            const translationId =
                queryOptions?.translationId?.toString() ||
                this.querySelector(
                    '.rz-aside__langs button.rz-button--selected',
                )?.getAttribute('data-translation-id') ||
                ''

            const asideContainerId =
                `type-${data.tree_type || 'node'}-tree` +
                `-translation-${translationId || 'main'}`

            treeContainer.setAttribute('data-tree-id', asideContainerId)

            this.currentTranslationId =
                translationId !== '' ? Number(translationId) : null
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
