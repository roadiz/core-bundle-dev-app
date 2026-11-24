import Sortable from 'sortablejs/modular/sortable.core.esm.js'

import { fadeIn, fadeOut } from '../utils/animation'
import { get, post, type RouteName } from '~/utils/http'
import { pushErrorToast, pushToast } from '~/utils/toast'

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

    get positionRoute(): RouteName | null {
        if (this.entityType === 'node') return 'nodesPositionAjax'
        if (this.entityType === 'tag') return 'tagPositionAjax'
        if (this.entityType === 'folder') return 'foldersPositionAjax'

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
        this.bindExpandButtonsId()
        this.addEventListener('command', this.onCommand)

        window.addEventListener(
            'requestAllNodeTreeChange',
            this.onRequestAllNodeTreeChange,
        )
        window.addEventListener(
            'requestAllNodeTreeRefresh',
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
        window.removeEventListener(
            'requestAllNodeTreeRefresh',
            this.onRequestAllNodeTreeChange,
        )
    }

    onRequestAllNodeTreeChange() {
        // Tag and folder trees would be replaced by a node tree response
        if (this.entityType && this.entityType !== 'node') return

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

    quickAddNode(
        nodeTypeName: string,
        parentNodeId: number,
        translationId: number | null,
    ) {
        return post('nodesGenerateAndAddNodeAction', {
            body: { nodeTypeName, parentNodeId, translationId },
        })
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
            pushErrorToast(error)
        }
    }

    fetchNodeTree() {
        const rootNodeId = this.getRootNodeId()

        return get<{ nodeTree?: string }>('nodesTreeAjax', {
            query: {
                _action: 'requestNodeTree',
                translationId: this.getRootTranslationId() ?? '',
                stackTree: this.hasAttribute('data-stack-tree')
                    ? true
                    : undefined,
                parentNodeId: rootNodeId ?? undefined,
                linkedTypes: this.getRootLinkedTypes(),
            },
        })
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

                    // nextTree binds its own expand buttons in connectedCallback
                    this.replaceWith(nextTree)
                    await fadeIn(nextTree)
                } else {
                    this.innerHTML = data.nodeTree
                    this.rootNode = this.querySelector('[role="tree"]')
                    this.bindExpandButtonsId()
                    await fadeIn(this)
                }

                window.Rozier?.lazyload?.bindAjaxLink?.()
            }
        } catch (error) {
            pushErrorToast(error)
        } finally {
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
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

    bindExpandButtonsId() {
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

        const postData: Record<string, number> = { id: parsedId }

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
            const data = await post<{
                responseText?: string
                detail?: string
                status?: 'success' | 'danger'
            }>(this.positionRoute, { body: postData })

            const message =
                this.entityType === 'node'
                    ? data.responseText || data.detail
                    : data.responseText

            pushToast(message, data.status)

            await this.refreshOtherTrees()
        } catch (error) {
            pushErrorToast(error)
        }

        return true
    }

    destroySortable() {
        if (!this.sortables.length) return

        this.sortables.forEach((sortable) => sortable.destroy())
        this.sortables = []
    }
}
