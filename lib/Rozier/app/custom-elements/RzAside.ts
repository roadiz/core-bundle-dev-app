import { fadeIn, fadeOut } from '~/utils/animation'
import { sleep } from '~/utils/sleep'

export default class RzAside extends HTMLElement {
    private currentTranslationId: string | null = null
    private entityType: null | string = null

    constructor() {
        super()

        this.onPageShowEnd = this.onPageShowEnd.bind(this)
        this.onCommand = this.onCommand.bind(this)
    }

    private get innerElement() {
        return this.querySelector('.rz-aside__body') as HTMLElement | null
    }

    connectedCallback() {
        this.refreshAsideMainTree()

        this.currentTranslationId = this.getAttribute(
            'data-default-translation-id',
        )
        window.addEventListener('pageshowend', this.onPageShowEnd)
        this.addEventListener('command', this.onCommand)
    }

    disconnectedCallback() {
        window.removeEventListener('pageshowend', this.onPageShowEnd)
        this.removeEventListener('command', this.onCommand)
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--update-translation':
                this.onLangButtonClick(event)
                break
        }
    }

    private onPageShowEnd() {
        this.refreshAsideMainTree()
    }

    private onLangButtonClick(event: CommandEvent) {
        const target = event.source as HTMLButtonElement | undefined
        if (!target || !this.entityType) {
            return
        }
        const translationId = target.getAttribute('data-translation-id')

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        if (this.entityType === 'node') {
            this.refreshMainNodeTree(translationId)
        } else if (this.entityType === 'folder') {
            this.refreshMainFolderTree(translationId)
        } else if (this.entityType === 'tag') {
            this.refreshMainTagTree(translationId)
        }
    }

    async refreshMainNodeTree(translationId: string | undefined = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes?.nodesTreeAjax || null,
            { translationId },
        )
    }

    async refreshMainTagTree(translationId: string | undefined = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes?.tagsTreeAjax || null,
            { translationId },
        )
    }

    async refreshMainFolderTree(translationId: string | undefined = undefined) {
        await this.refreshAsideMainTree(
            window.RozierConfig.routes?.foldersTreeAjax || null,
            { translationId },
        )
    }

    async refreshAsideMainTree(
        baseUrl: string | null = null,
        query: Record<string, string> = {},
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

    async refreshTreeContent(treeHTML = '') {
        if (!treeHTML) {
            console.warn('No treeHTML provided to refreshTreeContent')
            return
        }

        const innerElement = this.innerElement
        if (!innerElement) {
            console.warn('No tree container found to refreshTreeContent')
            return
        }

        const previousElement = innerElement.querySelector('.rz-tree-wrapper')

        const temporaryElement = document.createElement('div')
        temporaryElement.innerHTML = treeHTML
        const newElement = temporaryElement.querySelector('.rz-tree-wrapper')

        await fadeOut(innerElement)

        if (newElement) {
            innerElement.appendChild(newElement)
        }
        if (previousElement) {
            previousElement.remove()
        }

        await fadeIn(innerElement)
        window.Rozier?.lazyload?.bindAjaxLink?.()
    }

    getDisplayedTreeTranslationId() {
        return (
            this.getAttribute('data-translation-id') ||
            this.innerElement
                .querySelector('.rz-tree__list')
                ?.getAttribute('data-translation-id')
        )
    }

    async refreshMainTree(
        baseUrl?: string | null,
        queryOptions: Record<string, string> = {},
    ) {
        const options = {
            _token: window.RozierConfig.ajaxToken,
            _action: 'requestMainTree',
            url: window.location.pathname,
            ...queryOptions,
        }

        const translationId =
            queryOptions?.translationId ||
            this.getDisplayedTreeTranslationId() ||
            this.currentTranslationId

        if (translationId) {
            Object.assign(options, { translationId })
        }

        const query = new URLSearchParams(options)
        const fetchUrl =
            baseUrl ||
            (window.RozierConfig.routes as { treeAjaxGateway?: string })
                ?.treeAjaxGateway

        if (!fetchUrl) return
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
            // refresh only when new tree will be diferent (translation or type changed)
            const isSameType = data.tree_type === this.entityType
            const isSameTranslation =
                translationId === this.currentTranslationId
            if (isSameType && isSameTranslation) {
                return
            }

            await this.refreshTreeContent(treeHTML)

            this.entityType = data.tree_type
            this.currentTranslationId = translationId
        }
    }
}
