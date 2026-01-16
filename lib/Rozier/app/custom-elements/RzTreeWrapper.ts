import { fadeIn, fadeOut } from '~/utils/animation'
import { sleep } from '~/utils/sleep'

export class RzTreeWrapper extends HTMLElement {
    constructor() {
        super()

        this.onLangButtonClicked = this.onLangButtonClicked.bind(this)
    }

    async getTreeData(translationId = undefined) {
        const currentRootTree = this.querySelector('.root-tree')
        if (currentRootTree && !translationId) {
            translationId = currentRootTree.getAttribute('data-translation-id')
        }

        const type = this.getAttribute('type')
        let action = ''
        let baseUrl = ''
        let responseDataKey = ''

        if (type === 'node') {
            action = 'requestMainNodeTree'
            baseUrl = window.RozierConfig.routes.nodesTreeAjax
            responseDataKey = 'nodeTree'
        } else if (type === 'tag') {
            action = 'requestMainTagTree'
            baseUrl = window.RozierConfig.routes.tagsTreeAjax
            responseDataKey = 'tagTree'
        } else if (type === 'folder') {
            action = 'requestMainFolderTree'
            baseUrl = window.RozierConfig.routes.foldersTreeAjax
            responseDataKey = 'folderTree'
        }

        const query = new URLSearchParams({
            _token: window.RozierConfig.ajaxToken,
            _action: action,
            translationId: translationId || null,
        })

        const response = await fetch(`${baseUrl}?${query.toString()}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        if (!response.ok) {
            throw response
        }
        const data = await response.json()

        return {
            ...data,
            htmlString: data?.[responseDataKey] as string,
        }
    }

    async refreshTree(translationId = undefined) {
        console.log('RzTreeWrapper.refreshTree', translationId)

        let htmlString: string | null = null
        try {
            const response = await this.getTreeData(translationId)
            htmlString = response.htmlString
            console.log('refreshTree', response)
        } catch (e) {
            console.debug('[Rozier.refreshTree] Retrying in 3 seconds', e)
            // Wait for background jobs to be done
            await sleep(3000)
            this.getTreeData(translationId)
        }

        const previousTree =
            this.querySelector('[data-tree-placeholder]') ||
            this.querySelector('.rz-tree') ||
            this.lastElementChild

        await fadeOut(previousTree)
        previousTree.insertAdjacentHTML('beforebegin', htmlString)
        previousTree.remove()
        /* Find element previously inserted */
        const newTree = this.querySelector('.rz-tree')
        if (newTree instanceof HTMLElement) {
            await fadeIn(newTree)
        } else {
            console.debug('No main node-tree available.')
            return
        }

        window.Rozier?.initNestables?.()
        window.Rozier?.bindMainTrees?.()
        window.Rozier?.resize?.()
        window.Rozier?.lazyload?.bindAjaxLink()
        // console.log(window.Rozier)

        // window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    onLangButtonClicked(event: Event) {
        const targetBtn = event.currentTarget as HTMLElement
        const translationId = targetBtn?.getAttribute('data-translation-id')

        if (!translationId) return

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))
        this.refreshTree(parseInt(translationId, 10))
    }

    bindLangButtons(bind = true) {
        const langButton = this.querySelectorAll<HTMLButtonElement>(
            '.rz-language-switcher button[data-translation-id]',
        )

        langButton.forEach((button) => {
            const action = bind ? 'addEventListener' : 'removeEventListener'
            button[action]('click', this.onLangButtonClicked)
        })
    }

    async connectedCallback() {
        console.log('rz tree wrapper connected', this.getAttribute('type'))

        this.bindLangButtons(true)
        // await this.refreshTree()
    }

    disconnectedCallback() {
        console.log('rz tree wrapper disconnected')
        this.bindLangButtons(false)
    }
}
