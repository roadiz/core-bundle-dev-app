import { Popover, ATTRIBUTES_OPTIONS } from '~/utils/Popover'

type Position = 'first' | 'last'
type UpdatePayloadDict = Record<string, string | number | boolean | null>

export default class RzNodeTreeContextualMenu extends HTMLElement {
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
        const attr = this.getAttribute('data-node-id')
        return attr ? parseInt(attr) : null
    }

    get nodePathFetch() {
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
        const stored = window.sessionStorage.getItem('rozier_copied_node_id')
        return stored ? parseInt(stored) : null
    }

    get targetButton(): HTMLElement | null {
        return this.querySelector('[popovertarget]') as HTMLElement | null
    }

    get popoverElement(): HTMLElement | null {
        return this.querySelector('[data-contextual-menu-popover]')
    }

    get isPopoverFetched() {
        return (
            this.popoverElement?.getAttribute('data-popover-content-state') ===
            'fetched'
        )
    }

    connectedCallback() {
        if (!this.nodePathFetch) {
            console.warn(
                'NodeTreeContextualMenu: missing data-contextual-menu-path',
            )
            return
        }

        if (!this.popoverElement) {
            const popoverPlaceholder = document.createElement('div')
            popoverPlaceholder.id =
                this.targetButton?.getAttribute('popovertarget') || ''
            popoverPlaceholder.setAttribute('popover', '')
            popoverPlaceholder.setAttribute(
                'data-popover-content-state',
                'idle',
            )
            popoverPlaceholder.setAttribute('data-contextual-menu-popover', '')
            this.appendChild(popoverPlaceholder)
        }

        this.addEventListener('command', this.onCommand)

        this.popoverInstance = new Popover(this, {
            popoverElement: this.popoverElement!,
            onOpen: this.onPopoverOpen,
        })
    }

    disconnectedCallback(): void {
        this.popoverInstance?.destroy()
        this.popoverInstance = null

        this.removeEventListener('command', this.onCommand)
    }

    onPopoverOpen() {
        if (!this.isPopoverFetched) {
            this.replacePopoverContent()
        }
    }

    /*
     * Fetch contextual menu DOM if not already present
     */
    async replacePopoverContent() {
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        // TODO: add loading indicator
        const response = await fetch(this.nodePathFetch, {
            headers: {
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        this.popoverElement!.innerHTML = await response.text()
        this.popoverElement!.setAttribute(
            'data-popover-content-state',
            'fetched',
        )

        // contextualMenu.html.twig hasn't access to generated instance ID
        this.popoverElement!.querySelectorAll('button[command]').forEach(
            (button) => {
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
            },
        )

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
            this.popoverElement!.setAttribute(
                'data-popover-content-state',
                'need-update',
            )

            await this.replacePopoverContent()
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

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

    async pasteInsideNode() {
        await this.pasteNode('parentNodeId')
    }

    async pasteAfterNode() {
        await this.pasteNode('prevNodeId')
    }

    async pasteNode(propName: 'prevNodeId' | 'parentNodeId' = 'parentNodeId') {
        if (!Number.isSafeInteger(this.copiedNodeId)) {
            return
        }
        if (!Number.isSafeInteger(this.nodeId)) {
            return
        }
        const payload: UpdatePayloadDict = {
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

    async moveNodeToPosition(
        position: Position | undefined,
        event: CommandEvent,
    ) {
        if (!Number.isSafeInteger(this.nodeId)) {
            return
        }
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        const nodeTreeElement = (event.currentTarget as HTMLElement).closest(
            '.rz-tree__item',
        )

        const parentNodeId = parseInt(
            nodeTreeElement.closest('ul')?.getAttribute('data-parent-id') || '',
        )

        const postData: UpdatePayloadDict = {
            csrfToken: window.RozierConfig.ajaxToken,
            id: this.nodeId,
        }

        // Force to first position
        if (typeof position !== 'undefined' && position === 'first') {
            Object.assign(postData, { firstPosition: true })
        } else if (typeof position !== 'undefined' && position === 'last') {
            Object.assign(postData, { lastPosition: true })
        }

        if (typeof parentNodeId === 'number') {
            Object.assign(postData, { newParentId: parentNodeId })
        }

        try {
            await this.postNodeUpdate(this.editPositionPath, postData)
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

    async postNodeUpdate(url: string | null, postData: UpdatePayloadDict) {
        if (!url) return
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    // Required to prevent using this route as referer when login again
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams(
                    Object.entries(postData).reduce(
                        (acc, [key, value]) => {
                            acc[key] = value === null ? '' : String(value)
                            return acc
                        },
                        {} as Record<string, string>,
                    ),
                ),
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
                    new CustomEvent('requestAllNodeTreeChange', {
                        detail: {
                            nodeId: this.nodeId,
                            treeElement: this.closest('rz-tree'),
                        },
                    }),
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
