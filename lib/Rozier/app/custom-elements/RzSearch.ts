import type { RzDialog } from './RzDialog'
import api from '~/api'

const SEARCH_QUERY = 'search_all'

const SEARCH_REQUEST_EVENT = 'rz-search-request'
const SEARCH_SUCCESS_EVENT = 'rz-search-success'
const SEARCH_FAILED_EVENT = 'rz-search-failed'
const SEARCH_RESET_EVENT = 'rz-search-reset'

export class RzSearch extends HTMLElement {
    value: string | null = null
    searchInput: HTMLInputElement | null = null
    dialogElement: RzDialog | null = null

    constructor() {
        super()
        this.onInputChange = this.onInputChange.bind(this)
    }

    dispatchSearchEvent(
        eventName: string,
        detail: Record<string, unknown> = {},
    ) {
        const event = new CustomEvent(eventName, {
            detail,
            bubbles: true,
            composed: true,
        })
        this.dispatchEvent(event)
    }

    async nodesSourceSearchUpdate(searchTerms: string = '') {
        if (!searchTerms || searchTerms.length <= 1) {
            this.dispatchSearchEvent(SEARCH_RESET_EVENT)
            return
        }

        this.dispatchSearchEvent(SEARCH_REQUEST_EVENT, { searchTerms })

        try {
            const items = await api.getNodesSourceFromSearch(searchTerms)

            this.dispatchSearchEvent(SEARCH_SUCCESS_EVENT, { items })
        } catch (error) {
            this.dispatchSearchEvent(SEARCH_FAILED_EVENT, { error })
        }
    }

    onInputChange() {
        const newValue = this.searchInput?.value || ''
        this.value = newValue

        this.nodesSourceSearchUpdate(newValue)
    }

    getQueryParamsValue() {
        const urlParams = new URLSearchParams(window.location.search)
        return urlParams.get(SEARCH_QUERY)
    }

    connectedCallback() {
        this.dialogElement = this.querySelector<RzDialog>('[is="rz-dialog"]')
        const initialValue =
            this.getAttribute('initial-value') || this.getQueryParamsValue()

        this.searchInput = this.querySelector('input[type="search"]')
        if (this.searchInput) {
            this.searchInput?.addEventListener('input', this.onInputChange)
        }

        if (this.dialogElement && !!initialValue) {
            this.dialogElement.showDialog()
            this.searchInput.value = initialValue
        }

        this.addEventListener(SEARCH_REQUEST_EVENT, (e: CustomEvent) => {
            console.log('Search request:', e.detail.searchTerms)
        })

        this.addEventListener(SEARCH_SUCCESS_EVENT, (e: CustomEvent) => {
            console.log('Search success:', e.detail.items)
        })

        this.addEventListener(SEARCH_FAILED_EVENT, (e: CustomEvent) => {
            console.log('Search failed:', e.detail.error)
        })

        this.addEventListener(SEARCH_RESET_EVENT, () => {
            console.log('Search reset')
        })
    }

    disconnectedCallback() {
        // Remove event listener from search input
        this.searchInput?.removeEventListener('input', this.onInputChange)
    }

    /* Open the search dialog with `Cmd | ctrl + k`  */
    onKeyDown(event: KeyboardEvent, dialog: HTMLDialogElement) {
        if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
            event.preventDefault()
            dialog.showModal()
        }
    }

    initKeyBindEvent(dialog: HTMLDialogElement) {
        window.addEventListener('keydown', (event) =>
            this.onKeyDown(event, dialog),
        )
    }

    disposeKeyBindEvent(dialog: HTMLDialogElement) {
        window.removeEventListener('keydown', (event) =>
            this.onKeyDown(event, dialog),
        )
    }
}
