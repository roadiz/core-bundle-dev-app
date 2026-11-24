import { Popover, ATTRIBUTES_OPTIONS } from '~/utils/Popover'
import { get, post, type RouteName } from '~/utils/http'
import { pushErrorToast } from '~/utils/toast'

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

        let html: string
        try {
            // TODO: add loading indicator
            html = await get<string>(this.nodePathFetch, { accept: 'html' })
        } catch (error) {
            pushErrorToast(error)
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
            return
        }

        this.popoverElement!.innerHTML = html
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

        // Close before any request to prevent triggering the same action twice
        this.popoverElement?.hidePopover()
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        try {
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
                    await this.moveNodeToPosition('first')
                    break
                case '--move-last':
                    await this.moveNodeToPosition('last')
                    break
            }
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

    async changeStatus(event: CommandEvent) {
        const target = event.source as HTMLElement | undefined
        const statusName = target?.getAttribute('data-status')
        const statusValue = target?.getAttribute('data-value')

        if (!statusName || !statusValue) return

        try {
            await this.postNodeUpdate('nodesStatusesAjax', {
                nodeId: this.nodeId,
                statusName: statusName,
                statusValue: statusValue,
            })
        } finally {
            // Force to reload contextual menu content on next opening to update action buttons
            this.popoverElement!.setAttribute(
                'data-popover-content-state',
                'need-update',
            )
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
            nodeId: this.copiedNodeId,
        }
        payload[propName] = this.nodeId

        if (!(await this.postNodeUpdate('nodesPasteAjax', payload))) return
        window.sessionStorage.removeItem('rozier_copied_node_id')
    }

    async duplicateNode() {
        if (!Number.isSafeInteger(this.nodeId)) {
            return
        }
        await this.postNodeUpdate('nodesDuplicateAjax', {
            nodeId: this.nodeId,
        })
    }

    async moveNodeToPosition(position: Position | undefined) {
        if (!Number.isSafeInteger(this.nodeId)) {
            return
        }

        const parentList = this.closest('.rz-tree__item')?.closest('ul')
        // Without its parent list, sending no newParentId would move the node to root
        if (!parentList) {
            console.warn('NodeTreeContextualMenu: missing parent tree list')
            return
        }

        // Empty data-parent-id means the node is at tree root
        const parsedParentId = parseInt(
            parentList.getAttribute('data-parent-id') || '',
            10,
        )
        const parentNodeId = Number.isNaN(parsedParentId)
            ? null
            : parsedParentId

        const postData: UpdatePayloadDict = {
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

        const data = await this.postNodeUpdate('nodesPositionAjax', postData)
        // The position route stores no flash message, show its response instead
        if (data?.responseText) {
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.responseText,
                        status: data.status,
                    },
                }),
            )
        }
    }

    /**
     * @returns the response data, or null once the failure is toasted
     */
    async postNodeUpdate(route: RouteName, postData: UpdatePayloadDict) {
        try {
            const data = await post<{
                responseText?: string
                status?: string
            }>(route, { body: postData })
            window.dispatchEvent(new CustomEvent('requestAllNodeTreeChange'))
            // Show the flash messages the action stored in session
            window.dispatchEvent(new CustomEvent('requestMessagesRefresh'))
            return data
        } catch (error) {
            pushErrorToast(error)
            return null
        }
    }
}
