export default class NodeTreeContextActions {
    constructor() {
        this.onClick = this.onClick.bind(this)
        this.moveNodeToPosition = this.moveNodeToPosition.bind(this)

        document.querySelectorAll('.tree-contextualmenu').forEach((contextualMenu) => {
            const links = contextualMenu.querySelectorAll('.node-actions a')
            const nodeMoveFirstLink = contextualMenu.querySelector('a.move-node-first-position')
            const nodeMoveLastLink = contextualMenu.querySelector('a.move-node-last-position')
            this.bind(links, nodeMoveFirstLink, nodeMoveLastLink)
        })
    }

    bind(links, nodeMoveFirstLink, nodeMoveLastLink) {
        links.forEach((link) => {
            link.removeEventListener('click', this.onClick)
            link.addEventListener('click', this.onClick)
        })
        if (nodeMoveFirstLink) {
            nodeMoveFirstLink.removeEventListener('click', (e) => this.moveNodeToPosition('first', e))
            nodeMoveFirstLink.addEventListener('click', (e) => this.moveNodeToPosition('first', e))
        }
        if (nodeMoveLastLink) {
            nodeMoveLastLink.removeEventListener('click', (e) => this.moveNodeToPosition('last', e))
            nodeMoveLastLink.addEventListener('click', (e) => this.moveNodeToPosition('last', e))
        }
    }

    unbind() { }

    async onClick(event) {
        event.preventDefault()

        let link = event.currentTarget
        let element = link.closest('.nodetree-element')
        let nodeId = parseInt(element.getAttribute('data-node-id'))
        let statusName = link.getAttribute('data-status')
        let statusValue = link.getAttribute('data-value')
        let action = link.getAttribute('data-action')

        if (typeof action !== 'undefined') {
            window.Rozier.lazyload.canvasLoader.show()

            if (typeof statusName !== 'undefined' && typeof statusValue !== 'undefined') {
                // Change node status
                await this.changeStatus(nodeId, statusName, statusValue)
            } else {
                // Other actions
                if (action === 'duplicate') {
                    await this.duplicateNode(nodeId)
                }
            }
        }
    }

    async changeStatus(nodeId, statusName, statusValue) {
        await this.postNodeUpdate(window.RozierConfig.routes.nodesStatusesAjax, {
            _token: window.RozierConfig.ajaxToken,
            _action: 'nodeChangeStatus',
            nodeId: nodeId,
            statusName: statusName,
            statusValue: statusValue,
        })
        window.Rozier.lazyload.canvasLoader.hide()
    }

    /**
     * Move a node to the position.
     *
     * @param nodeId
     */
    async duplicateNode(nodeId) {
        await this.postNodeUpdate(window.RozierConfig.routes.nodeAjaxEdit.replace('%nodeId%', nodeId), {
            _token: window.RozierConfig.ajaxToken,
            _action: 'duplicate',
            nodeId: nodeId,
        })
        window.Rozier.lazyload.canvasLoader.hide()
    }

    /**
     * Move a node to the position.
     *
     * @param {String} position
     * @param {Event} event
     */
    async moveNodeToPosition(position, event) {
        window.Rozier.lazyload.canvasLoader.show()

        let element = event.currentTarget.closest('.nodetree-element')
        let nodeId = parseInt(element.getAttribute('data-node-id'))
        let parentNodeId = parseInt(element.closest('ul').getAttribute('data-parent-node-id'))
        let postData = {
            _token: window.RozierConfig.ajaxToken,
            _action: 'updatePosition',
            nodeId: nodeId,
        }

        /*
         * Force to first position
         */
        if (typeof position !== 'undefined' && position === 'first') {
            postData.firstPosition = true
        } else if (typeof position !== 'undefined' && position === 'last') {
            postData.lastPosition = true
        }

        /*
         * When dropping to root
         * set parentNodeId to NULL
         */
        if (isNaN(parentNodeId)) {
            parentNodeId = null
        }

        postData.newParent = parentNodeId

        await this.postNodeUpdate(window.RozierConfig.routes.nodeAjaxEdit.replace('%nodeId%', nodeId), postData)
        window.Rozier.lazyload.canvasLoader.hide()
    }

    async postNodeUpdate(url, postData) {
        return new Promise(async (resolve, reject) => {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                    },
                    body: new URLSearchParams(postData),
                })
                if (!response.ok) {
                    const data = await response.json()
                    window.UIkit.notify({
                        message: data.error_message,
                        status: 'danger',
                        timeout: 3000,
                        pos: 'top-center',
                    })
                    reject(data)
                } else {
                    const data = await response.json()
                    window.Rozier.refreshAllNodeTrees()
                    window.Rozier.getMessages()
                    resolve(data)
                }
            } catch (err) {
                reject(err)
            }
        })
    }
}
