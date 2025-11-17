export default class NodeTreeContextualMenu extends HTMLElement {
    get nodeId() {
        return this.getAttribute('data-node-id')
            ? parseInt(this.getAttribute('data-node-id'))
            : null
    }

    get contextualMenuPath() {
        return this.getAttribute('data-contextual-menu-path')
    }

    get editPositionPath() {
        return this.getAttribute('data-node-edit-position-path')
    }

    get duplicatePath() {
        return this.getAttribute('data-node-duplicate-path')
    }

    get pastePath() {
        return this.getAttribute('data-node-paste-path')
    }

    get statusPath() {
        return this.getAttribute('data-node-status-path')
    }

    get copiedTrans() {
        return this.getAttribute('data-node-copied-trans')
    }

    get copiedNodeId() {
        return window.sessionStorage.getItem('rozier_copied_node_id')
            ? parseInt(window.sessionStorage.getItem('rozier_copied_node_id'))
            : null
    }

    connectedCallback() {
        const button = this.querySelector('.tree-contextualmenu-button')
        const route = this.contextualMenuPath

        if (!button || !route) {
            return
        }

        this.onClick = this.onClick.bind(this)
        this.moveNodeToPosition = this.moveNodeToPosition.bind(this)

        button.addEventListener('click', async () => {
            const contextualMenuNav = this.querySelector('nav.uk-dropdown')

            /*
             * Fetch contextual menu DOM if not already present
             */
            if (!contextualMenuNav) {
                window.dispatchEvent(new CustomEvent('requestLoaderShow'))
                const contextualMenuDom = await fetch(route, {
                    headers: {
                        // Required to prevent using this route as referer when login again
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                this.insertAdjacentHTML(
                    'beforeend',
                    await contextualMenuDom.text(),
                )
                window.dispatchEvent(new CustomEvent('requestLoaderHide'))
            }

            const pasteButtons = this.querySelectorAll('.paste-node')
            pasteButtons.forEach((pasteButton) => {
                if (pasteButton && !Number.isInteger(this.copiedNodeId)) {
                    pasteButton.setAttribute('disabled', 'disabled')
                }
                if (pasteButton && Number.isInteger(this.copiedNodeId)) {
                    pasteButton.removeAttribute('disabled')
                    pasteButton.removeEventListener('click', this.onClick)
                    pasteButton.addEventListener('click', this.onClick)
                }
            })

            const copyButton = this.querySelector('.copy-node')
            if (copyButton) {
                copyButton.removeEventListener('click', this.onClick)
                copyButton.addEventListener('click', this.onClick)
            }

            const actions = this.querySelectorAll(
                '.node-actions a, .node-actions button, .duplicate-node',
            )
            actions.forEach((action) => {
                action.removeEventListener('click', this.onClick)
                action.addEventListener('click', this.onClick)
            })

            const nodeMoveFirstLink = this.querySelector(
                '.move-node-first-position',
            )
            if (nodeMoveFirstLink) {
                nodeMoveFirstLink.addEventListener('click', (e) =>
                    this.moveNodeToPosition('first', e),
                )
            }

            const nodeMoveLastLink = this.querySelector(
                '.move-node-last-position',
            )
            if (nodeMoveLastLink) {
                nodeMoveLastLink.addEventListener('click', (e) =>
                    this.moveNodeToPosition('last', e),
                )
            }
        })
    }

    async onClick(event) {
        event.preventDefault()

        const element = event.currentTarget

        const statusName = element.getAttribute('data-status')
        const statusValue = element.getAttribute('data-value')
        const action = element.getAttribute('data-action')

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        if (action === 'duplicate') {
            await this.duplicateNode()
            return
        }

        if (action === 'copy') {
            this.copyNode()
            return
        }

        if (action === 'paste_inside') {
            await this.pasteInsideNode()
            return
        }

        if (action === 'paste_after') {
            await this.pasteAfterNode()
            return
        }

        if (statusName !== '' && statusValue !== '') {
            // Change node status
            await this.changeStatus(statusName, statusValue)
            return
        }
    }

    async changeStatus(statusName, statusValue) {
        try {
            await this.postNodeUpdate(this.statusPath, {
                csrfToken: window.RozierConfig.ajaxToken,
                nodeId: this.nodeId,
                statusName: statusName,
                statusValue: statusValue,
            })
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

    /**
     * Copy a node ID in SessionStorage for further paste action.
     */
    copyNode() {
        window.sessionStorage.setItem(
            'rozier_copied_node_id',
            this.nodeId.toString(),
        )
        window.dispatchEvent(
            new CustomEvent('pushToast', {
                detail: {
                    message: this.copiedTrans || 'Node copied to clipboard',
                    status: 'success',
                },
            }),
        )
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    /**
     * Paste a node ID from SessionStorage.
     */
    async pasteInsideNode() {
        await this.pasteNode('parentNodeId')
    }

    /**
     * Paste a node ID from SessionStorage.
     */
    async pasteAfterNode() {
        await this.pasteNode('prevNodeId')
    }

    /**
     * Paste a node ID from SessionStorage.
     *
     * @param {'prevNodeId'|'parentNodeId'} propName
     */
    async pasteNode(propName = 'parentNodeId') {
        if (!Number.isSafeInteger(this.copiedNodeId)) {
            return
        }
        if (!Number.isSafeInteger(this.nodeId)) {
            return
        }
        const payload = {
            csrfToken: window.RozierConfig.ajaxToken,
            nodeId: this.copiedNodeId,
        }
        payload[propName] = this.nodeId

        try {
            await this.postNodeUpdate(this.pastePath, payload)
            window.sessionStorage.removeItem('rozier_copied_node_id')
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

    /**
     * Duplicate a node in the same parent.
     */
    async duplicateNode() {
        if (!Number.isSafeInteger(this.nodeId)) {
            return
        }
        try {
            await this.postNodeUpdate(this.duplicatePath, {
                csrfToken: window.RozierConfig.ajaxToken,
                nodeId: this.nodeId,
            })
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

    /**
     * Move a node to the position.
     *
     * @param {String} position
     * @param {Event} event
     */
    async moveNodeToPosition(position, event) {
        if (!Number.isSafeInteger(this.nodeId)) {
            return
        }
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        let element = event.currentTarget.closest('.nodetree-element')
        let parentNodeId = parseInt(
            element.closest('ul').getAttribute('data-parent-node-id'),
        )
        let postData = {
            csrfToken: window.RozierConfig.ajaxToken,
            id: this.nodeId,
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

        postData.newParentId = parentNodeId

        try {
            await this.postNodeUpdate(this.editPositionPath, postData)
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

    async postNodeUpdate(url, postData) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    // Required to prevent using this route as referer when login again
                    'X-Requested-With': 'XMLHttpRequest',
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
                    }),
                )
                throw data
            } else {
                const data = await response.json()
                window.dispatchEvent(
                    new CustomEvent('requestAllNodeTreeChange'),
                )
                return data
            }
        } catch (error) {
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message:
                            error.detail ||
                            error.message ||
                            'postNodeUpdate: Unknown error',
                        status: 'danger',
                    },
                }),
            )
        }
    }
}
