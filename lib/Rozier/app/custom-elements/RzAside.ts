import { fadeIn, fadeOut } from '~/utils/animation'
import { get, HttpError, type RouteName } from '~/utils/http'
import { pushErrorToast } from '~/utils/toast'

export default class RzAside extends HTMLElement {
    private currentTranslationId: string | null = null
    private entityType: null | string = null

    constructor() {
        super()

        this.onPageShowEnd = this.onPageShowEnd.bind(this)
        this.onMainTreeRefresh = this.onMainTreeRefresh.bind(this)
        this.onCommand = this.onCommand.bind(this)
    }

    private get innerElement() {
        return this.querySelector('.rz-aside__body') as HTMLElement | null
    }

    connectedCallback() {
        this.currentTranslationId = this.getAttribute(
            'data-default-translation-id',
        )
        this.refreshAsideMainTree()

        window.addEventListener('pageshowend', this.onPageShowEnd)
        window.addEventListener(
            'requestMainTreeRefresh',
            this.onMainTreeRefresh,
        )
        this.addEventListener('command', this.onCommand)
    }

    disconnectedCallback() {
        window.removeEventListener('pageshowend', this.onPageShowEnd)
        window.removeEventListener(
            'requestMainTreeRefresh',
            this.onMainTreeRefresh,
        )
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

    private onMainTreeRefresh() {
        // The tree content changed: refresh even if type and translation did not
        this.refreshAsideMainTree(undefined, {}, true)
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
            'nodesTreeAjax',
            { translationId },
            true,
        )
    }

    async refreshMainTagTree(translationId: string | undefined = undefined) {
        await this.refreshAsideMainTree('tagsTreeAjax', { translationId }, true)
    }

    async refreshMainFolderTree(translationId: string | undefined = undefined) {
        await this.refreshAsideMainTree(
            'foldersTreeAjax',
            { translationId },
            true,
        )
    }

    async refreshAsideMainTree(
        route: RouteName = 'treeAjaxGateway',
        query: Record<string, string | undefined> = {},
        force = false,
    ) {
        try {
            await this.refreshMainTree(route, query, force)
        } catch (error) {
            // 403: the user may not access this tree, the aside stays without one
            if (!(error instanceof HttpError && error.status === 403)) {
                pushErrorToast(error)
            }
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
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
                ?.querySelector('.rz-tree__list')
                ?.getAttribute('data-translation-id')
        )
    }

    async refreshMainTree(
        route: RouteName,
        queryOptions: Record<string, string | undefined> = {},
        force = false,
    ) {
        const translationId =
            queryOptions?.translationId ||
            this.getDisplayedTreeTranslationId() ||
            this.currentTranslationId

        const data = await get<Record<string, string>>(route, {
            query: {
                _action: 'requestMainTree',
                url: window.location.pathname,
                ...queryOptions,
                translationId: translationId || undefined,
            },
        })

        const treeHTML =
            data?.['nodeTree'] ||
            data?.['tagTree'] ||
            data?.['folderTree'] ||
            data?.['tree']

        if (data && typeof treeHTML !== 'undefined') {
            // Unless forced, refresh only when the tree type or translation changed
            const isSameType = data.tree_type === this.entityType
            const isSameTranslation =
                translationId === this.currentTranslationId
            if (!force && isSameType && isSameTranslation) {
                return
            }

            await this.refreshTreeContent(treeHTML)

            this.entityType = data.tree_type
            this.currentTranslationId = translationId
        }
    }
}
