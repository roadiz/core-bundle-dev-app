import $ from 'jquery'
import NodeTreeContextActions from '../components/trees/NodeTreeContextActions'

/**
 * Children nodes field
 */
export default class ChildrenNodesField {
    constructor() {
        // Bind methods
        this.onQuickAddClick = this.onQuickAddClick.bind(this)

        this.$fields = $('[data-children-nodes-widget]')
        this.$nodeTrees = this.$fields.find('.nodetree-widget')
        this.$quickAddNodeButtons = this.$fields.find('.children-nodes-quick-creation a')

        this.cleanNodeTree()
    }

    unbind() {
        if (this.$quickAddNodeButtons.length) {
            this.$quickAddNodeButtons.off('click')
        }
    }

    cleanNodeTree() {
        this.$fields = $('[data-children-nodes-widget]')
        this.$nodeTrees = this.$fields.find('.nodetree-widget')
        // Remove lang switcher on stack trees
        this.$fields.find('.nodetree-langs').remove()
        if (this.$quickAddNodeButtons.length) {
            this.$quickAddNodeButtons.off('click')
            this.$quickAddNodeButtons.on('click', this.onQuickAddClick)
        }
    }

    treeAvailable() {
        let $nodeTree = this.$fields.find('.nodetree-widget')
        return !!$nodeTree.length
    }

    async quickAddNode(nodeTypeName, parentNodeId, translationId) {
        return new Promise(async (resolve, reject) => {
            const response = await fetch(window.Rozier.routes.nodesQuickAddAjax, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new URLSearchParams({
                    _token: window.Rozier.ajaxToken,
                    _action: 'quickAddNode',
                    nodeTypeName: nodeTypeName,
                    parentNodeId: parentNodeId,
                    translationId: translationId,
                }),
            })
            if (!response.ok) {
                reject(await response.json())
            } else {
                resolve(await response.json())
            }
        })
    }

    async onQuickAddClick(event) {
        event.preventDefault()
        let $link = $(event.currentTarget)
        let nodeTypeName = $link.attr('data-children-node-type')
        let parentNodeId = parseInt($link.attr('data-children-parent-node'))
        let translationId = parseInt($link.attr('data-translation-id'))

        if (nodeTypeName !== '' && parentNodeId > 0) {
            try {
                await this.quickAddNode(nodeTypeName, parentNodeId, translationId)
                window.Rozier.refreshMainNodeTree()
                window.Rozier.getMessages()
                let $nodeTree = $link.parents('.children-nodes-widget').find('.nodetree-widget').eq(0)
                await this.refreshNodeTree($nodeTree)
            } catch (data) {
                data = JSON.parse(data.responseText)
                window.UIkit.notify({
                    message: data.error_message,
                    status: 'danger',
                    timeout: 3000,
                    pos: 'top-center',
                })
            }
        }
    }

    async fetchNodeTree(rootNodeId, linkedTypes, translationId = undefined) {
        return new Promise(async (resolve, reject) => {
            const params = new URLSearchParams({
                _token: window.Rozier.ajaxToken,
                _action: 'requestNodeTree',
                parentNodeId: rootNodeId,
                translationId: translationId || null,
            })
            linkedTypes.forEach((type, i) => {
                params.append('linkedTypes[' + i + ']', type)
            })
            const url = window.Rozier.routes.nodesTreeAjax + '?' + params.toString()
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
        })
    }

    /**
     * @param $nodeTree
     */
    async refreshNodeTree($nodeTree) {
        if (!$nodeTree.length) {
            console.log('No node-tree available.')
            return
        }

        let linkedTypes = []
        let $rootTree = $nodeTree.find('.root-tree').eq(0)
        if (!$rootTree) {
            return
        }
        const rootNodeId = parseInt($rootTree.attr('data-parent-node-id'))
        const linkedTypesRaw = $rootTree.attr('data-linked-types')
        let translationId = $rootTree.attr('data-translation-id')
        if (linkedTypesRaw) {
            linkedTypes = JSON.parse(linkedTypesRaw)
        }

        window.Rozier.lazyload.canvasLoader.show()
        try {
            const data = await this.fetchNodeTree(rootNodeId, linkedTypes, translationId || null)
            if ($nodeTree.length && typeof data.nodeTree !== 'undefined') {
                await window.Rozier.fadeOut($nodeTree)
                let $tempContainer = $nodeTree.parents('.children-nodes-widget')
                $nodeTree.replaceWith(data.nodeTree)

                $nodeTree = $tempContainer.find('.nodetree-widget')
                window.Rozier.initNestables()
                window.Rozier.bindMainTrees()
                window.Rozier.lazyload.bindAjaxLink()
                await window.Rozier.fadeIn($nodeTree)
                this.cleanNodeTree()

                window.Rozier.lazyload.canvasLoader.hide()

                if (window.Rozier.lazyload.nodeTreeContextActions) {
                    window.Rozier.lazyload.nodeTreeContextActions.unbind()
                }

                window.Rozier.lazyload.nodeTreeContextActions = new NodeTreeContextActions()
            }
        } catch (data) {
            data = JSON.parse(data.responseText)
            window.UIkit.notify({
                message: data.error_message,
                status: 'danger',
                timeout: 3000,
                pos: 'top-center',
            })
        }
    }
}
