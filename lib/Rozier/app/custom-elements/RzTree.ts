import Sortable from 'sortablejs/modular/sortable.core.esm.js'

import { fadeIn, fadeOut } from '../utils/animation'

export default class RzTree extends HTMLElement {
    sortables: Sortable[]
    rootNode: HTMLElement | null = null

    constructor() {
        super()

        this.sortables = []
        this.onSortableChange = this.onSortableChange.bind(this)
        this.onCommand = this.onCommand.bind(this)
        this.onRequestAllNodeTreeChange =
            this.onRequestAllNodeTreeChange.bind(this)
    }

    get group() {
        return this.getAttribute('group') || 'tree'
    }

    get positionRoute() {
        const attr = this.getAttribute('data-position-route')
        if (attr) return attr

        if (!window.RozierConfig || !window.RozierConfig.routes) {
            return null
        }

        if (this.entityType === 'node') {
            return window.RozierConfig.routes.nodesPositionAjax
        } else if (this.entityType === 'tag') {
            return window.RozierConfig.routes.tagPositionAjax
        } else if (this.entityType === 'folder') {
            return window.RozierConfig.routes.foldersPositionAjax
        }

        return null
    }

    get entityType() {
        return this.getAttribute('data-entity-type')
    }

    get isSortable() {
        return (
            this.getAttribute('data-is-sortable') !== 'false' &&
            !!this.positionRoute
        )
    }

    get expandedStateKey() {
        const base = 'collapsed.rz_tree'
        const treeId = this.getAttribute('data-tree-id') || this.id || 'root'

        return `${base}.${treeId}`
    }

    connectedCallback() {
        this.rootNode = this.querySelector('[role="tree"]')

        if (this.isSortable) {
            this.initSortable()
        }

        this.syncCollapsedState()
        this.bindExpandButtons()
        this.addEventListener('command', this.onCommand)

        window.addEventListener(
            'requestAllNodeTreeChange',
            this.onRequestAllNodeTreeChange,
        )
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
        this.destroySortable()

        window.removeEventListener(
            'requestAllNodeTreeChange',
            this.onRequestAllNodeTreeChange,
        )
    }

    onRequestAllNodeTreeChange() {
        this.refreshNodeTree()
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--toggle-children':
                this.onToggleChildren(event)
                break
            case '--quick-add-child-node':
                // Add child from children-nodes-widget (form) context
                this.onQuickAddNode(event)
                break
        }
    }

    get rootList() {
        return this.querySelector('.rz-tree__list') as HTMLElement | null
    }

    getRootLinkedTypes() {
        const rootList = this.rootList
        if (!rootList) return []

        const linkedTypesRaw = rootList.getAttribute('data-linked-types')
        if (!linkedTypesRaw) return []

        try {
            return JSON.parse(linkedTypesRaw) as string[]
        } catch {
            return []
        }
    }

    getRootNodeId() {
        const rootNodeId = this.rootList?.getAttribute('data-parent-id')
        if (!rootNodeId) return null

        const parsedId = parseInt(rootNodeId, 10)
        return Number.isNaN(parsedId) ? null : parsedId
    }

    getRootTranslationId() {
        const rootList = this.rootList
        if (!rootList) return null

        const translationId = rootList.getAttribute('data-translation-id')
        if (!translationId) return null

        const parsedId = parseInt(translationId, 10)
        return Number.isNaN(parsedId) ? null : parsedId
    }

    async quickAddNode(
        nodeTypeName: string,
        parentNodeId: number,
        translationId: number | null,
    ) {
        const response = await fetch(
            window.RozierConfig.routes.nodesGenerateAndAddNodeAction,
            {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    // Required to prevent using this route as referer when login again
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({
                    csrfToken: window.RozierConfig.ajaxToken,
                    nodeTypeName: nodeTypeName,
                    parentNodeId: parentNodeId.toString(),
                    translationId: translationId
                        ? translationId.toString()
                        : '',
                }),
            },
        )

        if (!response.ok) {
            throw response
        }

        return await response.json()
    }

    async onQuickAddNode(event: CommandEvent) {
        event.preventDefault()
        const button = event.source as HTMLElement | undefined
        if (!button) return

        const nodeTypeName = button.getAttribute('data-children-node-type')
        const parentNodeId = parseInt(
            button.getAttribute('data-children-parent-node') || '',
            10,
        )
        const translationIdRaw = button.getAttribute('data-translation-id')
        const translationId = translationIdRaw
            ? parseInt(translationIdRaw, 10)
            : null

        if (!nodeTypeName || Number.isNaN(parentNodeId) || parentNodeId <= 0) {
            return
        }

        try {
            await this.quickAddNode(nodeTypeName, parentNodeId, translationId)
            window.dispatchEvent(new CustomEvent('requestMainTreeRefresh'))
            window.dispatchEvent(new CustomEvent('requestMessagesRefresh'))
            await this.refreshNodeTree()
        } catch (error) {
            await this.pushRequestError(error)
        }
    }

    async fetchNodeTree() {
        const linkedTypes = this.getRootLinkedTypes()
        const translationId = this.getRootTranslationId()

        const options = {
            _token: window.RozierConfig.ajaxToken,
            _action: 'requestNodeTree',
            translationId: translationId ? translationId.toString() : '',
        }

        const rootNodeId = this.getRootNodeId()
        if (rootNodeId) {
            Object.assign(options, { parentNodeId: rootNodeId.toString() })
        }
        const params = new URLSearchParams(options)

        linkedTypes.forEach((type, i) => {
            params.append(`linkedTypes[${i}]`, type)
        })

        const url =
            window.RozierConfig.routes.nodesTreeAjax + '?' + params.toString()
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        if (!response.ok) {
            throw response
        }

        return await response.json()
    }

    async refreshOtherTrees() {
        const instances = document.querySelectorAll<RzTree>('rz-tree')
        const refreshPromises = Array.from(instances)
            .filter((instance) => instance !== this)
            .map((instance) => instance.refreshNodeTree())
        await Promise.all(refreshPromises)
    }

    async refreshNodeTree() {
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        try {
            const data = await this.fetchNodeTree()

            if (typeof data.nodeTree !== 'undefined') {
                const wrapper = document.createElement('div')
                wrapper.innerHTML = data.nodeTree
                const nextTree = wrapper.querySelector('rz-tree')

                await fadeOut(this)

                if (nextTree) {
                    const currentClasses = this.getAttribute('class')
                    if (currentClasses) {
                        const nextClasses = nextTree.getAttribute('class') || ''
                        const mergedClasses = new Set(
                            `${nextClasses} ${currentClasses}`
                                .trim()
                                .split(/\s+/),
                        )
                        nextTree.setAttribute(
                            'class',
                            Array.from(mergedClasses).join(' '),
                        )
                    }

                    Array.from(this.attributes).forEach((attr) => {
                        if (attr.name === 'class') return
                        nextTree.setAttribute(attr.name, attr.value)
                    })

                    this.replaceWith(nextTree)
                    await fadeIn(nextTree)
                } else {
                    this.innerHTML = data.nodeTree
                    this.rootNode = this.querySelector('[role="tree"]')
                    this.bindExpandButtons()
                    await fadeIn(this)
                }

                // window.dispatchEvent(new CustomEvent('requestNestablesInit'))
                // window.dispatchEvent(new CustomEvent('requestBindMainTrees'))
                // window.dispatchEvent(new CustomEvent('requestAjaxLinkBind'))
            }
        } catch (error) {
            await this.pushRequestError(error)
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }

    async pushRequestError(error: unknown) {
        let message = 'An unexpected error occurred.'

        try {
            if (error && typeof (error as Response).json === 'function') {
                const data = await (error as Response).json()
                message = data?.error_message || data?.detail || message
            } else if (
                error &&
                typeof (error as { responseText?: string }).responseText ===
                    'string'
            ) {
                const data = JSON.parse(
                    (error as { responseText: string }).responseText,
                )
                message = data?.error_message || message
            } else if (error && typeof error === 'object') {
                const errorData = error as {
                    error_message?: string
                    detail?: string
                }
                message = errorData.error_message || errorData.detail || message
            }
        } catch (error) {
            console.error('Error parsing error response', error)
        }

        window.dispatchEvent(
            new CustomEvent('pushToast', {
                detail: {
                    message,
                    status: 'danger',
                },
            }),
        )
    }

    // Children visibility
    onToggleChildren(event: CommandEvent) {
        const btn = event.source as HTMLButtonElement | undefined
        const isExpanded = btn.getAttribute('aria-expanded') === 'true'
        const newValue = !isExpanded
        btn.setAttribute('aria-expanded', newValue.toString())

        const item = btn.closest('li.rz-tree__item')
        if (!item) return
        this.updateCollapsedState(item, newValue)
    }

    bindExpandButtons() {
        const buttons = this.querySelectorAll('.rz-tree__item__expand-button')
        if (!buttons.length) return

        buttons.forEach((btn) => {
            if (btn.hasAttribute('commandfor')) return

            btn.setAttribute('commandfor', this.id)
        })
    }

    // SORTABLE
    initSortable() {
        const sortableLists = this?.querySelectorAll('.rz-tree__list')

        if (!sortableLists) return

        for (let i = 0; i < sortableLists.length; i++) {
            this.sortables.push(
                new Sortable(sortableLists[i], {
                    group: this.group,
                    animation: 150,
                    filter: '.rz-tree__item--locked',
                    handle: '.rz-tree__item__handle',
                    chosenClass: 'rz-tree__item--chosen',
                    dragClass: 'rz-tree__item--drag',
                    ghostClass: 'rz-tree__item--ghost',
                    onAdd: this.onSortableChange,
                    onUpdate: this.onSortableChange,
                    onRemove: () => false,
                }),
            )
        }
    }

    syncCollapsedState() {
        const expandedIds = this.getExpandedIds()
        if (!expandedIds.length) return

        expandedIds.forEach((itemId) => {
            const item = this.querySelector(
                `.rz-tree__item[data-entity-id="${itemId}"]`,
            )
            if (!item) return

            const btn = item.querySelector('.rz-tree__item__expand-button')
            btn?.setAttribute('aria-expanded', 'true')
        })
    }

    updateCollapsedState(item: Element, expanded: boolean) {
        const state = this.getExpandedIds()
        const entityId = this.getEntityId(item)
        if (!entityId) {
            console.warn('Entity ID not found for item', item)
            return
        }

        if (expanded && !state.includes(entityId)) {
            state.push(entityId)
        } else if (!expanded) {
            const index = state.indexOf(entityId)
            if (index >= 0) {
                state.splice(index, 1)
            }
        }

        this.saveExpandedState(state)
    }

    getExpandedIds() {
        let state: string[] | null = null
        if (!window.localStorage) return []

        const rawState = window.localStorage.getItem(this.expandedStateKey)
        if (rawState) {
            try {
                state = JSON.parse(rawState)
            } catch {
                state = null
            }
        }

        if (!state) {
            state = []
            this.saveExpandedState(state)
        }

        return state
    }

    saveExpandedState(state: string[]) {
        if (!window.localStorage) return

        window.localStorage.setItem(
            this.expandedStateKey,
            JSON.stringify(state),
        )
    }

    getEntityId(element: Element) {
        return element.getAttribute('data-entity-id') || element.id
    }

    getParentId(listElement: Element | null) {
        if (!listElement) return null

        return listElement.getAttribute('data-parent-id')
    }

    getSiblingId(element: Element | null) {
        if (!element) return null

        const id = this.getEntityId(element)
        if (!id) return null

        const parsedId = parseInt(id, 10)
        return Number.isNaN(parsedId) ? null : parsedId
    }

    async onSortableChange(event: Sortable.SortableEvent) {
        const element = event.item as HTMLElement | null
        if (!element) return false

        const entityId = this.getEntityId(element)
        if (!entityId) return false

        const parsedId = parseInt(entityId, 10)
        if (Number.isNaN(parsedId)) return false

        let parentIdValue = this.getParentId(element.parentElement)
        if (parentIdValue === null && event.to) {
            parentIdValue = this.getParentId(event.to)
        }
        let parentId = parentIdValue ? parseInt(parentIdValue, 10) : null
        if (Number.isNaN(parentId)) {
            parentId = null
        }

        if (parsedId === parentId) {
            if (this.entityType === 'tag' || this.entityType === 'folder') {
                alert(`You cannot move a ${this.entityType} inside itself!`)
            }
            console.error(
                `You cannot move a ${this.entityType || 'item'} inside itself!`,
            )
            window.location.reload()
            return false
        }

        const token =
            this.getAttribute('data-ajax-token') ||
            window.RozierConfig.ajaxToken ||
            ''
        const postData: Record<string, string | number | null> = {
            csrfToken: token,
            id: parsedId,
        }

        if (this.entityType === 'node') {
            if (typeof parentId === 'number') {
                Object.assign(postData, { newParentId: parentId })
            }
        } else if (parentId) {
            postData.newParentId = parentId
        }

        const nextId = this.getSiblingId(element.nextElementSibling)
        if (nextId !== null) {
            postData.nextId = nextId
        } else {
            const prevId = this.getSiblingId(element.previousElementSibling)
            if (prevId !== null) {
                postData.prevId = prevId
            }
        }

        if (!this.positionRoute) {
            return false
        }

        try {
            const response = await fetch(this.positionRoute, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams(postData as Record<string, string>),
            })
            if (!response.ok) {
                throw response
            }
            const data = await response.json()

            const message =
                this.entityType === 'node'
                    ? data.responseText || data.detail
                    : data.responseText

            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message,
                        status: data.status,
                    },
                }),
            )

            await this.refreshOtherTrees()
        } catch (response) {
            const data = await response.json()
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.error_message || data.detail,
                        status: 'danger',
                    },
                }),
            )
        }

        return true
    }

    destroySortable() {
        if (!this.sortables.length) return

        this.sortables.forEach((sortable) => sortable.destroy())
        this.sortables = []
    }
}
