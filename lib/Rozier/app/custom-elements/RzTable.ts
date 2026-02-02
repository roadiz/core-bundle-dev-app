import Sortable from 'sortablejs/modular/sortable.core.esm.js'

export default class RzTable extends HTMLTableElement {
    private sortable: Sortable | null

    constructor() {
        super()
        this.sortable = null
        this.onSortableUpdate = this.onSortableUpdate.bind(this)
    }

    private get tbody() {
        return this.querySelector('tbody') as HTMLElement | null
    }

    private get isSortableEnabled() {
        return this.getAttribute('data-sortable') === 'true'
    }

    private get sortableHandle() {
        return this.getAttribute('data-sortable-handle') || '.rz-table__handle'
    }

    private get sortableDragClass() {
        return this.getAttribute('data-sortable-drag-class') || ''
    }

    private get sortableUrl() {
        return this.getAttribute('data-sortable-url')
    }

    connectedCallback() {
        if (!this.isSortableEnabled || !this.tbody) {
            return
        }

        this.sortable = new Sortable(this.tbody, {
            animation: 150,
            handle: this.sortableHandle,
            dragClass: this.sortableDragClass || undefined,
            onUpdate: this.onSortableUpdate,
        })
    }

    disconnectedCallback() {
        this.sortable?.destroy()
        this.sortable = null
    }

    private getSortableItemId(element: Element | null) {
        if (!element) return null
        const id = element.getAttribute('data-id')
        if (!id) return null
        const parsed = parseInt(id, 10)
        return Number.isNaN(parsed) ? null : parsed
    }

    private getSortableSiblingId(element: Element | null) {
        return this.getSortableItemId(element)
    }

    private async onSortableUpdate(event: Sortable.SortableEvent) {
        const row = event.item as HTMLElement | null
        const sortableUrl = this.sortableUrl
        if (!row || !sortableUrl) {
            return
        }

        const currentId = this.getSortableItemId(row)
        if (!currentId) {
            return
        }

        const payload: Record<string, string> = {
            csrfToken: window.RozierConfig.ajaxToken,
            id: currentId.toString(),
        }

        const prevId = this.getSortableSiblingId(row.previousElementSibling)
        const nextId = this.getSortableSiblingId(row.nextElementSibling)

        if (prevId !== null) {
            payload.prevId = prevId.toString()
        } else if (nextId !== null) {
            payload.nextId = nextId.toString()
        }

        try {
            const response = await fetch(sortableUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams(payload),
            })
            if (!response.ok) {
                throw response
            }
            const data = await response.json()
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.responseText,
                        status: data.status,
                    },
                }),
            )
        } catch (response) {
            const data = await (response as Response).json()
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.title || '',
                        status: 'danger',
                    },
                }),
            )
        }
    }
}
