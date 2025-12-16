import type { RzDialog } from './RzDialog'
import type { NodeSourceSearch } from '~/types/node-source-search'
import api from '~/api'
import { rzCardRenderer } from '~/utils/component-renderer/rzCard'
import { debounce } from 'lodash'

const SEARCH_QUERY = 'search_all'

export class RzSearch extends HTMLElement {
    value: string | null = null
    searchInput: HTMLInputElement | null = null

    dialogElement: RzDialog | null = null
    listELement: HTMLUListElement | null = null

    statusMessage: HTMLDivElement | null = null
    fetchStatus:
        | 'idle'
        | 'reset'
        | 'pending'
        | 'results'
        | 'no-results'
        | 'error' = 'idle'

    items: NodeSourceSearch[] | null = null

    constructor() {
        super()

        this.onInputChange = debounce(this.onInputChange.bind(this), 300)
        this.onKeyDown = this.onKeyDown.bind(this)
    }

    createStatusMessage() {
        this.statusMessage = document.createElement('div')
        this.statusMessage.classList.add('visually-hidden')
        this.statusMessage.setAttribute('aria-live', 'polite')
        this.statusMessage.setAttribute('role', 'status')
        this.statusMessage.setAttribute('aria-atomic', 'true')

        if (!this.listELement) return
        this.listELement.parentElement?.insertBefore(
            this.statusMessage,
            this.listELement,
        )
    }

    updateStatusMessage() {
        if (!this.statusMessage) return
        const itemLength = this.items?.length

        if (this.fetchStatus === 'idle') {
            this.statusMessage.textContent = 'Waiting for request'
        } else if (this.fetchStatus === 'reset') {
            this.statusMessage.textContent = 'Request reset'
        } else if (this.fetchStatus === 'pending') {
            this.statusMessage.textContent = 'Searching...'
        } else if (this.fetchStatus === 'results' && this.items !== null) {
            this.statusMessage.textContent = `${itemLength} result${itemLength > 1 ? 's' : ''} found`
        } else if (this.fetchStatus === 'no-results') {
            this.statusMessage.textContent = 'No results found'
        } else if (this.fetchStatus === 'error') {
            this.statusMessage.textContent =
                'An error occurred while fetching results'
        }
    }

    getItemsElement() {
        if (!this.items?.length) return []

        return this.items.map((item) => {
            const li = document.createElement('li')
            const card = rzCardRenderer({
                tag: item.editItem ? 'a' : 'div',
                title: item.displayable,
                overtitle: item.classname,
                image: {
                    src: item.thumbnail.url,
                    width: item.thumbnail.imageWidth,
                    height: item.thumbnail.imageHeight,
                    alt: item.thumbnail.alt || item.displayable,
                },
                attributes: {
                    href: item.editItem,
                },
            })
            li.appendChild(card)

            return li
        })
    }

    render() {
        this.updateStatusMessage()

        if (this.listELement) {
            this.listELement.innerHTML = ''
            this.getItemsElement()?.forEach((el) => {
                this.listELement?.appendChild(el)
            })
        }
    }

    async onInputChange() {
        const newValue = this.searchInput?.value || ''
        this.value = newValue

        if (!newValue || newValue.length <= 1) {
            this.fetchStatus = 'reset'
        } else {
            try {
                this.fetchStatus = 'pending'
                const items = await api.getNodesSourceFromSearch(newValue)

                if (items.length) {
                    this.fetchStatus = 'results'
                    this.items = items
                } else {
                    this.fetchStatus = 'no-results'
                    this.items = []
                }
            } catch {
                this.fetchStatus = 'error'
                this.items = null
            }
        }

        this.render()
    }

    getQueryParamsValue() {
        const urlParams = new URLSearchParams(window.location.search)
        return urlParams.get(SEARCH_QUERY)
    }

    connectedCallback() {
        this.listELement =
            this.querySelector<HTMLUListElement>('[data-search-list]')

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
            this.onInputChange()
        }

        const preventSubmitElement =
            this.querySelectorAll<HTMLElement>('[prevent-submit]')
        if (preventSubmitElement?.length) {
            preventSubmitElement.forEach((el) => {
                el.onsubmit = (e) => {
                    e.preventDefault()
                }
            })
        }

        if (this.hasAttribute('open-key')) {
            this.initKeyBindEvent()
        }

        this.createStatusMessage()
        this.render()
    }

    disconnectedCallback() {
        if (this.hasAttribute('open-key')) {
            this.disposeKeyBindEvent()
        }

        this.searchInput?.removeEventListener('input', this.onInputChange)
    }

    onKeyDown(event: KeyboardEvent) {
        const openKey = this.getAttribute('open-key')
        if (!openKey) return

        const keys = openKey.split('+').map((k) => k.trim().toLowerCase())
        const isValid = keys.every((key) => {
            const isMeta =
                (key === 'meta' && event.metaKey) ||
                (key === 'meta' && event.ctrlKey)
            return isMeta || key === event.key
        })

        if (isValid) {
            event.preventDefault()
            this.dialogElement.showModal()
        }
    }

    initKeyBindEvent() {
        window.addEventListener('keydown', this.onKeyDown)
    }

    disposeKeyBindEvent() {
        window.removeEventListener('keydown', this.onKeyDown)
    }
}
