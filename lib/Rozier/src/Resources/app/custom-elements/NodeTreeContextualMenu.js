export default class NodeTreeContextualMenu extends HTMLElement {
    get contextualMenuPath() {
        return this.getAttribute('data-contextual-menu-path');
    }
    get editPath() {
        return this.getAttribute('data-node-edit-path');
    }
    get statusPath() {
        return this.getAttribute('data-node-status-path');
    }

    connectedCallback() {
        const button = this.querySelector('.tree-contextualmenu-button')
        const route = this.contextualMenuPath

        if (!button || !route) {
            return
        }

        this.onClick = this.onClick.bind(this)
        this.moveNodeToPosition = this.moveNodeToPosition.bind(this)

        button.addEventListener('click', async (event) => {
            const contextualMenuNav = this.querySelector('nav.uk-dropdown')

            /*
             * Fetch contextual menu DOM if not already present
             */
            if (!contextualMenuNav) {
                window.dispatchEvent(new CustomEvent('requestLoaderShow'))
                const contextualMenuDom = await fetch(route)
                this.insertAdjacentHTML('beforeend', await contextualMenuDom.text())
                window.dispatchEvent(new CustomEvent('requestLoaderHide'))
            }

            const links = this.querySelectorAll('.node-actions a')
            const nodeMoveFirstLink = this.querySelector('a.move-node-first-position')
            const nodeMoveLastLink = this.querySelector('a.move-node-last-position')

            links.forEach((link) => {
                link.removeEventListener('click', this.onClick)
                link.addEventListener('click', this.onClick)
            })
            if (nodeMoveFirstLink) {
                nodeMoveFirstLink.addEventListener('click', (e) => this.moveNodeToPosition('first', e))
            }
            if (nodeMoveLastLink) {
                nodeMoveLastLink.addEventListener('click', (e) => this.moveNodeToPosition('last', e))
            }
        })
    }

    async onClick(event) {
        event.preventDefault()

        let link = event.currentTarget
        let element = link.closest('.nodetree-element')
        let nodeId = parseInt(element.getAttribute('data-node-id'))
        let statusName = link.getAttribute('data-status')
        let statusValue = link.getAttribute('data-value')
        let action = link.getAttribute('data-action')

        if (typeof action !== 'undefined') {
            window.dispatchEvent(new CustomEvent('requestLoaderShow'))

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
        await this.postNodeUpdate(this.statusPath, {
            _token: window.RozierConfig.ajaxToken,
            _action: 'nodeChangeStatus',
            nodeId: nodeId,
            statusName: statusName,
            statusValue: statusValue,
        })
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    /**
     * Move a node to the position.
     *
     * @param nodeId
     */
    async duplicateNode(nodeId) {
        await this.postNodeUpdate(this.editPath, {
            _token: window.RozierConfig.ajaxToken,
            _action: 'duplicate',
            nodeId: nodeId,
        })
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    /**
     * Move a node to the position.
     *
     * @param {String} position
     * @param {Event} event
     */
    async moveNodeToPosition(position, event) {
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

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

        await this.postNodeUpdate(this.editPath, postData)
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
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
                    window.dispatchEvent(
                        new CustomEvent('pushToast', {
                            detail: {
                                message: data.error_message,
                                status: 'danger',
                            },
                        })
                    )
                    reject(data)
                } else {
                    const data = await response.json()
                    window.dispatchEvent(new CustomEvent('requestAllNodeTreeChange'))
                    resolve(data)
                }
            } catch (err) {
                reject(err)
            }
        })
    }
}
