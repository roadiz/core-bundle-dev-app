import type { RzDialog } from './RzDialog'
import type { NodeSourceSearch } from '~/types/node-source-search'
import api from '~/api'
import { rzCardRenderer } from '~/utils/component-renderer/rzCard'
import { debounce } from 'lodash'

const SEARCH_QUERY = 'searchTerms'

/* Used for component documentation */
/*
type RzSearchAttributes = {
    'initial-value'?: string // Initial value to populate the search input
    'open-key'?: string // Key combination to open the search dialog (e.g., "Meta+K")
    'data-status-wrapper'?: string // Wrapper element for status message and spinner wrapper
    'data-results-wrapper'?: string // UL element to display search results
    'idle-text'?: string // Text to display when idle
    'reset-text'?: string // Text to display when search is reset
    'pending-text'?: string // Text to display when search is pending
    'unique-result-text'?: string // Text to display when exactly one result is found
    'results-text'?: string // Text to display when multiple results are found
    'no-results-text'?: string // Text to display when no results are found
    'error-text'?: string // Text to display when an error occurs
}
*/

export class RzSearch extends HTMLElement {
    value: string | null = null
    searchInput: HTMLInputElement | null = null

    dialogElement: RzDialog | null = null
    listElement: HTMLUListElement | null = null

    spinnerElement: HTMLElement | null = null
    statusElement: HTMLElement | null = null
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

    getStatusText() {
        switch (this.fetchStatus) {
            case 'idle':
                return this.getAttribute('idle-text') || ''
            case 'reset':
                return this.getAttribute('reset-text') || ''
            case 'pending':
                return this.getAttribute('pending-text') || ''
            case 'results':
                if (this.items?.length === 1) {
                    return this.getAttribute('unique-result-text') || ''
                } else if (this.items?.length && this.items.length > 1) {
                    const text = this.getAttribute('results-text') || ''
                    return text.replace('{n}', String(this.items.length))
                }
                return ''
            case 'no-results':
                return this.getAttribute('no-results-text') || ''
            case 'error':
                return this.getAttribute('error-text') || ''
            default:
                return ''
        }
    }

    setStatusText() {
        if (this.statusElement) {
            this.statusElement.textContent = this.getStatusText()
        }
    }

    createStatusElements() {
        let wrapper = this.querySelector('[data-status-wrapper]')
        const body = this.querySelector('[data-search-body]')

        if (!wrapper && body) {
            wrapper = document.createElement('div')
            wrapper.setAttribute('data-status-wrapper', '')
            body.appendChild(wrapper)
        }

        wrapper.setAttribute('aria-live', 'polite')
        wrapper.setAttribute('role', 'status')
        wrapper.setAttribute('aria-atomic', 'true')

        this.statusElement = document.createElement('div')
        this.statusElement.classList.add('rz-visually-hidden')
        this.setStatusText()
        wrapper.appendChild(this.statusElement)

        this.spinnerElement = document.createElement('div')
        this.spinnerElement.setAttribute('aria-hidden', 'true')
        this.spinnerElement.classList.add('rz-spinner', 'rz-spinner--lg')
        this.updateSpinnerVisibility()
        wrapper.appendChild(this.spinnerElement)

        this.render()
    }

    getItemsElement() {
        if (!this.items?.length) return []

        return this.items.map((item) => {
            const li = document.createElement('li')
            const imgSrc = item.thumbnail?.url
            const card = rzCardRenderer({
                tag: item.editItem ? 'a' : 'div',
                title: item.displayable,
                overtitle: item.classname,
                image: imgSrc
                    ? {
                          src: imgSrc,
                          width: item.thumbnail?.imageWidth,
                          height: item.thumbnail?.imageHeight,
                          alt: item.thumbnail?.alt || item.displayable,
                      }
                    : undefined,
                attributes: {
                    href: item.editItem,
                },
            })
            li.appendChild(card)

            return li
        })
    }

    render() {
        this.setStatusText()
        this.updateSpinnerVisibility()

        if (!this.listElement) return
        // Use DocumentFragment for batch rendering to minimize reflows
        const fragment = document.createDocumentFragment()
        this.getItemsElement()?.forEach((el) => {
            fragment.appendChild(el)
        })

        this.listElement.innerHTML = ''
        this.listElement.appendChild(fragment)
    }

    async fetchNodeSources(value: string) {
        try {
            this.fetchStatus = 'pending'
            this.render()

            const items = await api.getNodesSourceFromSearch(value)

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

    async onInputChange() {
        const newValue = this.searchInput?.value || ''
        this.value = newValue

        if (!newValue || newValue.length <= 1) {
            this.fetchStatus = 'reset'
            this.render()
        } else {
            await this.fetchNodeSources(newValue)
        }
    }

    getQueryParamsValue() {
        const urlParams = new URLSearchParams(window.location.search)
        return urlParams.get(SEARCH_QUERY)
    }

    connectedCallback() {
        this.listElement = this.querySelector<HTMLUListElement>(
            '[data-results-wrapper]',
        )
        const body = this.querySelector('[data-search-body]')
        if (!this.listElement && body) {
            this.listElement = document.createElement('ul')
            this.listElement.setAttribute('data-results-wrapper', '')
            body.appendChild(this.listElement)
        }

        this.dialogElement = this.querySelector<RzDialog>('[is="rz-dialog"]')

        const initialValue =
            this.getAttribute('initial-value') || this.getQueryParamsValue()

        this.searchInput = this.querySelector('input[type="search"]')
        if (!this.searchInput) {
            console.error('RzSearch: No search input found')
            return
        }
        this.searchInput.addEventListener('input', this.onInputChange)

        if (this.dialogElement && !!initialValue) {
            this.dialogElement.showDialog()
            this.searchInput.value = initialValue
            this.onInputChange()
        }

        const preventSubmitElement = this.querySelectorAll<HTMLElement>(
            '[data-prevent-submit]',
        )
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
            if (key === 'meta') return event.metaKey || event.ctrlKey
            if (key === 'ctrl') return event.ctrlKey
            if (key === 'alt') return event.altKey
            if (key === 'shift') return event.shiftKey

            return event.key.toLowerCase() === key
        })

        if (isValid && this.dialogElement) {
            event.preventDefault()
            this.dialogElement.showDialog()
        }
    }

    initKeyBindEvent() {
        window.addEventListener('keydown', this.onKeyDown)
    }

    disposeKeyBindEvent() {
        window.removeEventListener('keydown', this.onKeyDown)
    }
}
