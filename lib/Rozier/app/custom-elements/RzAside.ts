import RoadizElement from '~/utils/custom-element/RoadizElement'
import { fadeIn, fadeOut } from '~/utils/animation'
import { sleep } from '~/utils/sleep'

export default class RzAside extends RoadizElement {
    private currentTranslationId: number | null = null
    private entityType: null | string = null

    constructor() {
        super()

        this.onPageShowEnd = this.onPageShowEnd.bind(this)
        this.onCommand = this.onCommand.bind(this)
    }

    private get rozier() {
        return window.Rozier
    }

    private get treeContainer() {
        return this.querySelector('.rz-aside__body') as HTMLElement | null
    }

    connectedCallback() {
        this.refreshAsideMainTree()

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
        // TODO: refresh only when new tree will be diferent (translation or type changed)
        this.refreshAsideMainTree()
        const id = this.treeContainer.getAttribute('data-id')
        console.log('onPageShowEnd', id)
    }

    private onLangButtonClick(event: CommandEvent) {
        const target = event.source as HTMLButtonElement | undefined
        if (!target || !this.entityType) {
            return
        }
        const translationIdRaw = target.getAttribute('data-translation-id')
        const translationId = translationIdRaw
            ? parseInt(translationIdRaw, 10)
            : undefined

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        if (this.entityType === 'node') {
            this.refreshMainNodeTree(translationId)
        } else if (this.entityType === 'folder') {
            this.refreshMainFolderTree(translationId)
        } else if (this.entityType === 'tag') {
            this.refreshMainTagTree(translationId)
        }
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
            this.entityType = data.tree_type

            await this.refreshTreeContent(treeHTML)

            const translationId = this.getNewContainerId(
                data.tree_type,
                queryOptions?.translationId?.toString() ||
                    this.querySelector(
                        '.rz-aside__langs button.rz-button--selected',
                    )?.getAttribute('data-translation-id') ||
                    '',
            )

            const asideContainerId =
                `type-${this.entityType}-tree` +
                `-translation-${translationId || 'main'}`

            treeContainer.setAttribute('data-id', asideContainerId)

            this.currentTranslationId =
                translationId !== '' ? Number(translationId) : null
        }
    }

    getNewContainerId(type: string, translation: string) {
        return `type-${type}-tree-translation-${translation}`
    }
}
