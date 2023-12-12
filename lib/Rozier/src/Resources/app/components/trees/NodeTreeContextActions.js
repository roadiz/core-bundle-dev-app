import $ from 'jquery'

export default class NodeTreeContextActions {
    constructor() {
        this.$contextualMenus = $('.tree-contextualmenu')
        this.$links = this.$contextualMenus.find('.node-actions a')
        this.$nodeMoveFirstLinks = this.$contextualMenus.find('a.move-node-first-position')
        this.$nodeMoveLastLinks = this.$contextualMenus.find('a.move-node-last-position')

        this.onClick = this.onClick.bind(this)
        this.moveNodeToPosition = this.moveNodeToPosition.bind(this)

        if (this.$links.length) {
            this.bind()
        }
    }

    bind() {
        this.$links.on('click', this.onClick)
        this.$nodeMoveFirstLinks.on('click', (e) => this.moveNodeToPosition('first', e))
        this.$nodeMoveLastLinks.on('click', (e) => this.moveNodeToPosition('last', e))
    }

    unbind() {
        this.$links.off('click', this.onClick)
        this.$nodeMoveFirstLinks.off('click')
        this.$nodeMoveLastLinks.off('click')
    }

    async onClick(event) {
        event.preventDefault()

        let $link = $(event.currentTarget)
        let $element = $($link.parents('.nodetree-element')[0])
        let nodeId = parseInt($element.data('node-id'))
        let statusName = $link.attr('data-status')
        let statusValue = $link.attr('data-value')
        let action = $link.attr('data-action')

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
        await this.postNodeUpdate(window.Rozier.routes.nodesStatusesAjax, {
            _token: window.Rozier.ajaxToken,
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
        await this.postNodeUpdate(window.Rozier.routes.nodeAjaxEdit.replace('%nodeId%', nodeId), {
            _token: window.Rozier.ajaxToken,
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

        let element = $($(event.currentTarget).parents('.nodetree-element')[0])
        let nodeId = parseInt(element.data('node-id'))
        let parentNodeId = parseInt(element.parents('ul').first().data('parent-node-id'))
        let postData = {
            _token: window.Rozier.ajaxToken,
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

        await this.postNodeUpdate(window.Rozier.routes.nodeAjaxEdit.replace('%nodeId%', nodeId), postData)
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
                    await window.Rozier.refreshAllNodeTrees()
                    await window.Rozier.getMessages()
                    resolve(await response.json())
                }
            } catch (err) {
                reject()
            }
        })
    }
}
