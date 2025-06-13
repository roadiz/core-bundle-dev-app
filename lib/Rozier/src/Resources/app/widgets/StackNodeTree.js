import { fadeIn, fadeOut } from '../utils/animation'

export default class StackNodeTree {
    constructor() {
        this.page = document.querySelector('.stack-tree')
        if (this.page) {
            this.quickAddNodeButtons = this.page.querySelectorAll('.stack-tree-quick-creation a')
            this.nodeTree = this.page.querySelector('.root-tree')
        }

        this.onQuickAddClick = this.onQuickAddClick.bind(this)
        this.init()
    }

    /**
     * @return {Number}
     */
    getCurrentPage() {
        this.nodeTree = this.page.querySelector('.root-tree')
        const currentPage = parseInt(this.nodeTree.getAttribute('data-page'))
        return isNaN(currentPage) ? 1 : currentPage
    }

    /**
     * @return {Number|null}
     */
    getTranslationId() {
        this.nodeTree = this.page.querySelector('.root-tree')
        const translationId = parseInt(this.nodeTree.getAttribute('data-translation-id'))
        return isNaN(translationId) ? null : translationId
    }

    init() {
        if (this.page) {
            const langs = this.page.querySelector('.nodetree-langs')
            if (langs) langs.remove()

            this.quickAddNodeButtons.forEach((btn) => {
                btn.addEventListener('click', this.onQuickAddClick)
            })
        }
    }

    unbind() {
        if (this.page) {
            this.quickAddNodeButtons.forEach((btn) => {
                btn.removeEventListener('click', this.onQuickAddClick)
            })
        }
    }

    async quickAddNode(nodeTypeName, parentNodeId, tagId = null) {
        try {
            const response = await fetch(window.RozierConfig.routes.nodesQuickAddAjax, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new URLSearchParams({
                    _token: window.RozierConfig.ajaxToken,
                    _action: 'quickAddNode',
                    nodeTypeName,
                    parentNodeId,
                    translationId: this.getTranslationId(),
                    tagId,
                    pushTop: 1,
                }),
            })
            if (!response.ok) throw await response.json()
            return await response.json()
        } catch (err) {
            throw err
        }
    }

    async onQuickAddClick(event) {
        event.preventDefault()
        const link = event.currentTarget
        const nodeTypeName = link.getAttribute('data-children-node-type')
        const parentNodeId = parseInt(link.getAttribute('data-children-parent-node'))
        let tagId = null

        if (!nodeTypeName || parentNodeId <= 0) return false

        if (link.hasAttribute('data-filter-tag')) {
            tagId = parseInt(link.getAttribute('data-filter-tag'))
        }

        try {
            await this.quickAddNode(nodeTypeName, parentNodeId, tagId)
            await Promise.all([window.Rozier.refreshMainNodeTree(), this.refreshNodeTree(parentNodeId, tagId, 1)])
            await window.Rozier.getMessages()
        } catch (data) {
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.error_message,
                        status: 'danger',
                    },
                })
            )
        }
    }

    treeAvailable() {
        return !!(this.page && this.page.querySelector('.nodetree-widget'))
    }

    async fetchNodeTree(rootNodeId, page = undefined, tagId = undefined) {
        try {
            const params = new URLSearchParams({
                _token: window.RozierConfig.ajaxToken,
                _action: 'requestNodeTree',
                stackTree: true,
                parentNodeId: rootNodeId,
                page: page || this.getCurrentPage(),
                tagId,
                translationId: this.getTranslationId(),
            })
            const url = `${window.RozierConfig.routes.nodesTreeAjax}?${params.toString()}`
            const response = await fetch(url, {
                method: 'GET',
                headers: { Accept: 'application/json' },
            })
            if (!response.ok) throw await response.json()
            return await response.json()
        } catch (err) {
            throw err
        }
    }

    async refreshNodeTree(rootNodeId, tagId, page) {
        const nodeTreeContainer = this.page.querySelector('.nodetree-widget')
        if (!nodeTreeContainer) return

        const rootTree = nodeTreeContainer.querySelector('.root-tree')

        if (typeof rootNodeId === 'undefined') {
            const parentIdAttr = rootTree.getAttribute('data-parent-node-id')
            rootNodeId = parentIdAttr ? parseInt(parentIdAttr) : null
        } else {
            rootNodeId = parseInt(rootNodeId)
        }

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        const data = await this.fetchNodeTree(
            rootNodeId,
            page ? parseInt(page) : undefined,
            tagId ? parseInt(tagId) : undefined
        )

        if (data.nodeTree) {
            await fadeOut(nodeTreeContainer)

            const wrapper = document.createElement('div')
            wrapper.innerHTML = data.nodeTree
            const newNodeTree = wrapper.querySelector('.nodetree-widget')

            nodeTreeContainer.replaceWith(newNodeTree)

            window.Rozier.initNestables()
            window.Rozier.bindMainTrees()
            window.Rozier.lazyload.bindAjaxLink()
            await fadeIn(newNodeTree)
            window.Rozier.resize()

            this.nodeTree = this.page.querySelector('.root-tree')
            const langs = this.page.querySelector('.nodetree-langs')
            if (langs) langs.remove()
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }
}
