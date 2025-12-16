import type { RzDialog } from './RzDialog'
import type { NodeSourceSearch } from '~/types/node-source-search'
import api from '~/api'
import { rzCardRenderer } from '~/utils/component-renderer/rzCard'
import { debounce } from 'lodash'

const SEARCH_QUERY = 'search_all'

/* Used for component documentation */
type RzSearchAttributes = {
    'initial-value'?: string // Initial value to populate the search input
    'open-key'?: string // Key combination to open the search dialog (e.g., "Meta+K")
    'status-wrapper'?: string // Wrapper element for status message and spinner wrapper
    'results-wrapper'?: string // UL element to display search results
}

export class RzSearch extends HTMLElement {
    value: string | null = null
    searchInput: HTMLInputElement | null = null

    dialogElement: RzDialog | null = null
    listELement: HTMLUListElement | null = null

    spinnerElement: HTMLElement | null = null
    messageElement: HTMLElement | null = null
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

    updateSpinnerVisibility() {
        if (!this.spinnerElement) return

        if (this.fetchStatus === 'pending') {
            this.spinnerElement.style.display = 'initial'
        } else {
            this.spinnerElement.style.display = 'none'
        }
    }

    updateStatusMessage() {
        if (!this.messageElement) return

        if (this.fetchStatus === 'idle') {
            this.messageElement.textContent = 'Waiting for request'
        } else if (this.fetchStatus === 'reset') {
            this.messageElement.textContent = 'Request reset'
        } else if (this.fetchStatus === 'pending') {
            this.messageElement.textContent = 'Searching...'
        } else if (this.fetchStatus === 'results' && this.items !== null) {
            const itemLength = this.items?.length
            this.messageElement.textContent = `${itemLength} result${itemLength > 1 ? 's' : ''} found`
        } else if (this.fetchStatus === 'no-results') {
            this.messageElement.textContent = 'No results found'
        } else if (this.fetchStatus === 'error') {
            this.messageElement.textContent =
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
        this.updateSpinnerVisibility()
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
            this.render()
        } else {
            try {
                this.fetchStatus = 'pending'
                this.render()
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
            } finally {
                this.render()
            }
        }
    }

    createStatusElements() {
        const wrapper = this.querySelector('[status-wrapper]')
        if (!wrapper) return

        wrapper.setAttribute('aria-live', 'polite')
        wrapper.setAttribute('role', 'status')
        wrapper.setAttribute('aria-atomic', 'true')

        this.messageElement = document.createElement('div')
        this.messageElement.classList.add('visually-hidden')
        this.updateStatusMessage()
        wrapper.appendChild(this.messageElement)

        this.spinnerElement = document.createElement('div')
        this.spinnerElement.setAttribute('aria-hidden', 'true')
        this.spinnerElement.classList.add('rz-spinner', 'rz-spinner--lg')
        this.updateSpinnerVisibility()
        wrapper.appendChild(this.spinnerElement)

        this.render()
    }

    getQueryParamsValue() {
        const urlParams = new URLSearchParams(window.location.search)
        return urlParams.get(SEARCH_QUERY)
    }

    connectedCallback() {
        this.listELement =
            this.querySelector<HTMLUListElement>('[results-wrapper]')

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

        this.createStatusElements()
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
