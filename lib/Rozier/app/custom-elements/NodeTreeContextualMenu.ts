import { Popover, ATTRIBUTES_OPTIONS } from '~/utils/Popover'

// TODO: children tree view not updated after move actions

export default class NodeTreeContextualMenu extends HTMLElement {
    popoverInstance: Popover | null = null

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        this.popoverInstance?.updateOptions()
    }

    constructor() {
        super()

        this.onCommand = this.onCommand.bind(this)
        this.onPopoverOpen = this.onPopoverOpen.bind(this)
    }

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

    get targetButton() {
        return this.querySelector('[popovertarget]')
    }

    get contextualMenuPopover(): HTMLElement {
        return this.querySelector('[data-contextual-menu-popover]')
    }

    get isContextualMenuPopoverFetched() {
        return (
            this.contextualMenuPopover.getAttribute('data-fetched') === 'true'
        )
    }

    connectedCallback() {
        if (!this.contextualMenuPath) {
            console.warn(
                'NodeTreeContextualMenu: missing data-contextual-menu-path',
            )
            return
        }

        if (!this.contextualMenuPopover) {
            const popoverPlaceholder = document.createElement('div')
            popoverPlaceholder.id =
                this.targetButton.getAttribute('popovertarget') || ''
            popoverPlaceholder.setAttribute('popover', '')
            popoverPlaceholder.setAttribute('data-contextual-menu-popover', '')
            this.appendChild(popoverPlaceholder)
        }

        this.addEventListener('command', this.onCommand)

        this.popoverInstance = new Popover(this, {
            popoverElement: this.contextualMenuPopover,
            onOpen: this.onPopoverOpen,
        })
    }

    disconnectedCallback() {
        this.popoverInstance?.destroy()
        this.popoverInstance = null

        this.removeEventListener('command', this.onCommand)
    }

    onPopoverOpen() {
        if (!this.isContextualMenuPopoverFetched) {
            this.replacePopoverContent()
        }
    }

    /*
     * Fetch contextual menu DOM if not already present
     */
    async replacePopoverContent() {
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        // TODO: add loading indicator
        const contextualMenuDom = await fetch(this.contextualMenuPath, {
            headers: {
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        this.contextualMenuPopover.innerHTML = await contextualMenuDom.text()
        this.contextualMenuPopover.setAttribute('data-fetched', 'true')

        // contextualMenu.html.twig hasn't access to generated instance ID
        this.contextualMenuPopover
            .querySelectorAll('button[command]')
            .forEach((button) => {
                button.setAttribute('commandfor', this.id)

                const isPasteBtn =
                    button.getAttribute('command') === '--paste-inside' ||
                    button.getAttribute('command') === '--paste-after'

                if (isPasteBtn) {
                    if (Number.isInteger(this.copiedNodeId)) {
                        button.removeAttribute('disabled')
                        button.setAttribute(
                            'title',
                            'Stored node id: ' + this.copiedNodeId,
                        )
                    } else {
                        button.setAttribute('disabled', 'disabled')
                    }
                }
            })

        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    async onCommand(event: CommandEvent) {
        event.preventDefault()

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        switch (event.command) {
            case '--duplicate':
                await this.duplicateNode()
                break
            case '--copy':
                this.copyNode()
                break
            case '--paste-inside':
                await this.pasteInsideNode()
                break
            case '--paste-after':
                await this.pasteAfterNode()
                break
            case '--update-status':
                await this.changeStatus(event)
                break
            case '--move-first':
                await this.moveNodeToPosition('first', event)
                break
            case '--move-last':
                await this.moveNodeToPosition('last', event)
                break
        }
    }

    async changeStatus(event: CommandEvent) {
        const target = event.source as HTMLElement | undefined
        const statusName = target?.getAttribute('data-status')
        const statusValue = target?.getAttribute('data-value')

        if (!statusName || !statusValue) return

        try {
            await this.postNodeUpdate(this.statusPath, {
                csrfToken: window.RozierConfig.ajaxToken,
                nodeId: this.nodeId,
                statusName: statusName,
                statusValue: statusValue,
            })
        } finally {
            // Force to reload contextual menu content to update action buttons
            this.contextualMenuPopover.setAttribute(
                'data-fetched',
                'need-update',
            )

            await this.replacePopoverContent()
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

        const element = event.currentTarget.closest('.nodetree-element')

        let parentNodeId = parseInt(
            element.closest('ul').getAttribute('data-parent-node-id'),
        )
        const postData = {
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
