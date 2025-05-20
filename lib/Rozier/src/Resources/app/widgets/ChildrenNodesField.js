import NodeTreeContextActions from '../components/trees/NodeTreeContextActions'
import { fadeIn, fadeOut } from '../utils/animation'

/**
 * Children nodes field
 */
export default class ChildrenNodesField {
    constructor() {
        this.onQuickAddClick = this.onQuickAddClick.bind(this)

        this.nodeTrees = document.querySelectorAll('[data-children-nodes-widget] .nodetree-widget')
        this.quickAddNodeButtons = document.querySelectorAll('.children-nodes-quick-creation a')

        this.cleanNodeTree()
    }

    unbind() {
        this.quickAddNodeButtons.forEach((button) => {
            button.removeEventListener('click', this.onQuickAddClick)
        })
    }

    cleanNodeTree() {
        // Remove lang switcher on stack trees
        document.querySelectorAll('[data-children-nodes-widget] .nodetree-langs').forEach((element) => {
            element.remove()
        })
        this.quickAddNodeButtons.forEach((button) => {
            button.removeEventListener('click', this.onQuickAddClick)
            button.addEventListener('click', this.onQuickAddClick)
        })
    }

    treeAvailable() {
        return !!(this.nodeTrees && this.nodeTrees.length)
    }

    async quickAddNode(nodeTypeName, parentNodeId, translationId) {
        return new Promise(async (resolve, reject) => {
            const response = await fetch(window.RozierConfig.routes.nodesQuickAddAjax, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new URLSearchParams({
                    _token: window.RozierConfig.ajaxToken,
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
        let link = event.currentTarget
        let nodeTypeName = link.getAttribute('data-children-node-type')
        let parentNodeId = parseInt(link.getAttribute('data-children-parent-node'))
        let translationId = parseInt(link.getAttribute('data-translation-id'))

        if (nodeTypeName !== '' && parentNodeId > 0) {
            try {
                await this.quickAddNode(nodeTypeName, parentNodeId, translationId)
                window.Rozier.refreshMainNodeTree()
                window.Rozier.getMessages()
                let nodeTree = link.closest('.children-nodes-widget').querySelector('.nodetree-widget')
                await this.refreshNodeTree(nodeTree)
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
                _token: window.RozierConfig.ajaxToken,
                _action: 'requestNodeTree',
                parentNodeId: rootNodeId,
                translationId: translationId || null,
            })
            linkedTypes.forEach((type, i) => {
                params.append('linkedTypes[' + i + ']', type)
            })
            const url = window.RozierConfig.routes.nodesTreeAjax + '?' + params.toString()
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
     * @param nodeTree
     */
    async refreshNodeTree(nodeTree) {
        if (!nodeTree) {
            console.log('No node-tree available.')
            return
        }

        let linkedTypes = []
        const rootTree = nodeTree.querySelector('.root-tree')
        if (!rootTree) {
            return
        }
        const rootNodeId = parseInt(rootTree.getAttribute('data-parent-node-id'))
        const linkedTypesRaw = rootTree.getAttribute('data-linked-types')
        let translationId = rootTree.getAttribute('data-translation-id')
        if (linkedTypesRaw) {
            linkedTypes = JSON.parse(linkedTypesRaw)
        }

        window.Rozier.lazyload.canvasLoader.show()
        try {
            const data = await this.fetchNodeTree(rootNodeId, linkedTypes, translationId || null)
            if (nodeTree && typeof data.nodeTree !== 'undefined') {
                await fadeOut(nodeTree)
                let tempContainer = nodeTree.closest('.children-nodes-widget')
                nodeTree.innerHTML = data.nodeTree

                nodeTree = tempContainer.querySelector('.nodetree-widget')
                window.Rozier.initNestables()
                window.Rozier.bindMainTrees()
                window.Rozier.lazyload.bindAjaxLink()
                await fadeIn(nodeTree)
                this.cleanNodeTree()

                window.Rozier.lazyload.canvasLoader.hide()
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
