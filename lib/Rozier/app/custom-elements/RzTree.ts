import Sortable from 'sortablejs/modular/sortable.core.esm.js'

export default class RzTree extends HTMLElement {
    sortables: Sortable[]
    rootNode: HTMLElement | null = null

    constructor() {
        super()

        this.sortables = []
        this.onSortableChange = this.onSortableChange.bind(this)
        this.onCommand = this.onCommand.bind(this)
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
        this.querySelectorAll('.rz-tree__item__expand-button').forEach(
            (btn) => {
                if (btn.hasAttribute('commandfor')) return

                btn.setAttribute('commandfor', this.id)
            },
        )
        this.addEventListener('command', this.onCommand)
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
        this.destroySortable()
    }

    onToggleChildren(event: CommandEvent) {
        const btn = event.source as HTMLButtonElement | undefined
        const isExpanded = btn.getAttribute('aria-expanded') === 'true'
        const newValue = !isExpanded
        btn.setAttribute('aria-expanded', newValue.toString())

        const item = btn.closest('li.rz-tree__item')
        if (!item) return
        this.updateCollapsedState(item, newValue)
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--toggle-children':
                this.onToggleChildren(event)
                break
        }
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
            btn.setAttribute('aria-expanded', 'true')
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
