import Sortable from 'sortablejs/modular/sortable.core.esm.js'
import { post } from '~/utils/http'
import { pushErrorToast, pushToast } from '~/utils/toast'

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

        const payload: Record<string, number> = { id: currentId }

        const prevId = this.getSortableItemId(row.previousElementSibling)
        const nextId = this.getSortableItemId(row.nextElementSibling)

        if (prevId !== null) {
            payload.prevId = prevId
        } else if (nextId !== null) {
            payload.nextId = nextId
        }

        try {
            // sortable.url is a public rz_table option (docs/extensions/backoffice_templates.md)
            const data = await post<{
                responseText?: string
                status?: 'success' | 'danger'
            }>(sortableUrl, { body: payload })
            pushToast(data.responseText, data.status)
        } catch (error) {
            pushErrorToast(error)
        }
    }
}
