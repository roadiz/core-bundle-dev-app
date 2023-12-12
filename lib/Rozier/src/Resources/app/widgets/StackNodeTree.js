import $ from 'jquery'
import NodesBulk from '../components/bulk-edits/NodesBulk'
import NodeTreeContextActions from '../components/trees/NodeTreeContextActions'

export default class StackNodeTree {
    constructor() {
        this.$page = $('.stack-tree').eq(0)
        this.currentRequest = null
        this.$quickAddNodeButtons = this.$page.find('.stack-tree-quick-creation a')
        this.$nodeTree = this.$page.find('.root-tree').eq(0)

        this.onQuickAddClick = this.onQuickAddClick.bind(this)

        this.init()
    }

    /**
     * @return {Number}
     */
    getCurrentPage() {
        this.$nodeTree = this.$page.find('.root-tree').eq(0)
        let currentPage = parseInt(this.$nodeTree.attr('data-page'))
        if (isNaN(currentPage)) {
            return 1
        }

        return currentPage
    }

    /**
     * @return {Number|null}
     */
    getTranslationId() {
        this.$nodeTree = this.$page.find('.root-tree').eq(0)
        let currentTranslationId = parseInt(this.$nodeTree.attr('data-translation-id'))
        if (isNaN(currentTranslationId)) {
            return null
        }

        return currentTranslationId
    }

    init() {
        this.$page.find('.nodetree-langs').remove()

        if (this.$quickAddNodeButtons.length) {
            this.$quickAddNodeButtons.on('click', this.onQuickAddClick)
        }
    }

    unbind() {
        if (this.$quickAddNodeButtons.length) {
            this.$quickAddNodeButtons.off('click', this.onQuickAddClick)
        }
    }

    async quickAddNode(nodeTypeId, parentNodeId, tagId = undefined) {
        return new Promise(async (resolve, reject) => {
            try {
                const response = await fetch(window.Rozier.routes.nodesQuickAddAjax, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                    },
                    body: new URLSearchParams({
                        _token: window.Rozier.ajaxToken,
                        _action: 'quickAddNode',
                        nodeTypeId: nodeTypeId,
                        parentNodeId: parentNodeId,
                        translationId: this.getTranslationId(),
                        tagId: tagId || null,
                        pushTop: 1,
                    }),
                })
                if (!response.ok) {
                    reject(await response.json())
                } else {
                    resolve(await response.json())
                }
            } catch (err) {
                reject()
            }
        })
    }

    /**
     * @param {Event} event
     * @returns {boolean}
     */
    async onQuickAddClick(event) {
        event.preventDefault()
        let $link = $(event.currentTarget)
        let nodeTypeId = parseInt($link.attr('data-children-node-type'))
        let parentNodeId = parseInt($link.attr('data-children-parent-node'))
        let tagId = undefined

        if (nodeTypeId <= 0 || parentNodeId <= 0) {
            return false
        }

        if ($link.attr('data-filter-tag')) {
            tagId = parseInt($link.attr('data-filter-tag'))
        }

        try {
            await this.quickAddNode(nodeTypeId, parentNodeId, tagId)
            await Promise.all([window.Rozier.refreshMainNodeTree(), this.refreshNodeTree(parentNodeId, tagId, 1)])
            await window.Rozier.getMessages()
        } catch (data) {
            window.UIkit.notify({
                message: data.error_message,
                status: 'danger',
                timeout: 3000,
                pos: 'top-center',
            })
        }
    }

    treeAvailable() {
        let $nodeTree = this.$page.find('.nodetree-widget')
        return !!$nodeTree.length
    }

    async fetchNodeTree(rootNodeId, page = undefined, tagId = undefined) {
        return new Promise(async (resolve, reject) => {
            try {
                const params = new URLSearchParams({
                    _token: window.Rozier.ajaxToken,
                    _action: 'requestNodeTree',
                    stackTree: true,
                    parentNodeId: rootNodeId,
                    page: page || this.getCurrentPage(),
                    tagId: tagId,
                    translationId: this.getTranslationId(),
                })
                const url = `${window.Rozier.routes.nodesTreeAjax}?${params.toString()}`
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                })
                if (!response.ok) {
                    reject(await response.json())
                } else {
                    resolve(await response.json())
                }
            } catch (err) {
                reject()
            }
        })
    }

    /**
     * @param {Number|String|undefined|null} rootNodeId
     * @param {Number|String|undefined|null} tagId
     * @param page
     */
    async refreshNodeTree(rootNodeId, tagId, page) {
        let $nodeTree = this.$page.find('.nodetree-widget')
        if (!$nodeTree.length) {
            return
        }

        let $rootTree = $nodeTree.find('.root-tree').eq(0)

        if (typeof rootNodeId === 'undefined') {
            if (!$rootTree.attr('data-parent-node-id')) {
                rootNodeId = null
            } else {
                rootNodeId = parseInt($rootTree.attr('data-parent-node-id'))
            }
        } else {
            rootNodeId = parseInt(rootNodeId)
        }

        window.Rozier.lazyload.canvasLoader.show()

        const data = await this.fetchNodeTree(
            rootNodeId,
            page ? parseInt(page) : undefined,
            tagId ? parseInt(tagId) : undefined
        )
        if ($nodeTree.length && typeof data.nodeTree !== 'undefined') {
            await window.Rozier.fadeOut($nodeTree)
            $nodeTree.replaceWith(data.nodeTree)
            $nodeTree = this.$page.find('.nodetree-widget')

            window.Rozier.initNestables()
            window.Rozier.bindMainTrees()
            window.Rozier.lazyload.bindAjaxLink()
            await window.Rozier.fadeIn($nodeTree)
            window.Rozier.resize()

            /* eslint-disable no-new */
            new NodesBulk()

            this.$nodeTree = this.$page.find('.root-tree').eq(0)
            this.$page.find('.nodetree-langs').remove()
            window.Rozier.lazyload.canvasLoader.hide()

            if (window.Rozier.lazyload.nodeTreeContextActions) {
                window.Rozier.lazyload.nodeTreeContextActions.unbind()
            }

            window.Rozier.lazyload.nodeTreeContextActions = new NodeTreeContextActions()
        }
    }
}
