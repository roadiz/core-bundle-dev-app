import { Popover } from '~/utils/Popover'
import {
    rzDropdownItemRenderer,
    rzDropdownRenderer,
} from '~/utils/component-renderer/rzDropdown'

const MIN_TERM_LENGTH = 2
const DEBOUNCE_DELAY = 300
const ACTIVE_OPTION_CLASS = 'rz-dropdown__item--selected'

let instanceCount = 0

/**
 * Comma-separated multi-value autocomplete: suggests values for the last term typed
 * in a text input, in an `rz-dropdown` listbox placed under it. Follows the ARIA
 * combobox pattern: focus stays on the input, the active option is exposed through
 * `aria-activedescendant`.
 *
 * The host wraps the form holding the `.rz-autocomplete__input` text input and carries
 * `data-action` (`_action` sent to the search path: `tagAutocomplete`,
 * `folderAutocomplete`), `data-search-path` and `data-csrf-token`.
 */
export default class RzAutocomplete extends HTMLElement {
    private input: HTMLInputElement | null = null
    private menu: HTMLElement | null = null
    private list: HTMLElement | null = null
    private positioner: Popover | null = null
    private debounceTimer: number | null = null
    private abortController: AbortController | null = null
    private activeIndex = -1
    private readonly listId = `rz-autocomplete-${++instanceCount}`

    get action() {
        return this.getAttribute('data-action') || ''
    }

    get searchPath() {
        return this.getAttribute('data-search-path') || ''
    }

    get csrfToken() {
        return this.getAttribute('data-csrf-token') || ''
    }

    connectedCallback() {
        this.input = this.querySelector<HTMLInputElement>(
            '.rz-autocomplete__input',
        )
        if (!this.input || !this.action || this.menu) return

        this.createMenu(this.input)

        this.input.setAttribute('autocomplete', 'off')
        this.input.setAttribute('role', 'combobox')
        this.input.setAttribute('aria-autocomplete', 'list')
        this.input.setAttribute('aria-controls', this.listId)
        // The Popover util keeps aria-expanded in sync on the input (its target) when the menu toggles
        this.input.setAttribute('aria-expanded', 'false')

        this.input.addEventListener('input', this.onInput)
        this.input.addEventListener('keydown', this.onKeyDown)
        this.input.addEventListener('blur', this.onBlur)
    }

    disconnectedCallback() {
        this.cancelPendingSearch()

        this.input?.removeEventListener('input', this.onInput)
        this.input?.removeEventListener('keydown', this.onKeyDown)
        this.input?.removeEventListener('blur', this.onBlur)

        this.positioner?.destroy()
        this.positioner = null
        this.menu?.remove()
        this.menu = null
        this.list = null
    }

    private createMenu(input: HTMLInputElement) {
        // A single, empty list: options are added once suggestions come back
        const menu = rzDropdownRenderer({ items: [[]], listTag: 'ul' })
        menu.classList.add('rz-autocomplete__menu')
        menu.setAttribute('popover', 'manual')

        const list = menu.querySelector<HTMLElement>('.rz-dropdown__list')
        if (!list) return

        list.id = this.listId
        list.setAttribute('role', 'listbox')
        this.labelList(list, input)
        // Keep the focus on the input when picking an option or scrolling the list
        list.addEventListener('mousedown', this.onListMouseDown)

        menu.appendChild(list)
        this.appendChild(menu)

        this.menu = menu
        this.list = list
        this.positioner = new Popover(this, {
            targetElement: input,
            popoverElement: menu,
            placement: 'bottom-start',
            offset: 4,
        })
    }

    private labelList(list: HTMLElement, input: HTMLInputElement) {
        const label = input.labels?.[0]
        if (label) {
            label.id ||= `${this.listId}-label`
            list.setAttribute('aria-labelledby', label.id)
            return
        }

        const name = input.getAttribute('aria-label') || input.placeholder
        if (name) list.setAttribute('aria-label', name)
    }

    private get options() {
        return Array.from(
            this.list?.querySelectorAll<HTMLElement>('[role="option"]') || [],
        )
    }

    private get isOpen() {
        return !!this.menu?.matches(':popover-open')
    }

    private onInput = () => {
        this.cancelPendingSearch()

        const term = this.lastTerm(this.input?.value || '')
        if (term.length < MIN_TERM_LENGTH) {
            this.close()
            return
        }

        // Always debounce fetch calls triggered by user input
        this.debounceTimer = window.setTimeout(
            () => this.search(term),
            DEBOUNCE_DELAY,
        )
    }

    private async search(term: string) {
        this.abortController = new AbortController()
        const params = new URLSearchParams({
            _action: this.action,
            _token: this.csrfToken,
            search: term,
        })

        try {
            const response = await fetch(`${this.searchPath}?${params}`, {
                headers: {
                    // Required to prevent using this route as referer when login again
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: this.abortController.signal,
            })
            if (!response.ok) throw new Error(response.statusText)

            const suggestions: unknown = await response.json()
            this.show(Array.isArray(suggestions) ? suggestions.map(String) : [])
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return
            }
            this.close()
        }
    }

    private cancelPendingSearch() {
        if (this.debounceTimer) {
            window.clearTimeout(this.debounceTimer)
            this.debounceTimer = null
        }
        this.abortController?.abort()
        this.abortController = null
    }

    private show(suggestions: string[]) {
        if (!this.input || !this.menu || !this.list) return

        this.list.replaceChildren(
            ...suggestions.map((suggestion, index) =>
                this.createOption(suggestion, index),
            ),
        )
        this.setActive(-1)

        // The field may have been left while the request was pending
        if (!suggestions.length || document.activeElement !== this.input) {
            this.close()
            return
        }

        this.menu.style.setProperty(
            '--rz-dropdown-min-width',
            `${this.input.offsetWidth}px`,
        )
        if (!this.isOpen) this.menu.showPopover()
    }

    private createOption(suggestion: string, index: number) {
        return rzDropdownItemRenderer({
            tag: 'li',
            label: suggestion,
            attributes: {
                id: `${this.listId}-option-${index}`,
                role: 'option',
                'aria-selected': 'false',
            },
        })
    }

    private close() {
        this.setActive(-1)
        if (this.isOpen) this.menu?.hidePopover()
    }

    private setActive(index: number) {
        const options = this.options
        this.activeIndex = index

        options.forEach((option, optionIndex) => {
            const isActive = optionIndex === index
            option.classList.toggle(ACTIVE_OPTION_CLASS, isActive)
            option.setAttribute('aria-selected', String(isActive))
        })

        const activeOption = options[index]
        if (activeOption) {
            this.input?.setAttribute('aria-activedescendant', activeOption.id)
            activeOption.scrollIntoView({ block: 'nearest' })
        } else {
            this.input?.removeAttribute('aria-activedescendant')
        }
    }

    private select(index: number) {
        const option = this.options[index]
        if (!this.input || !option) return

        const terms = this.split(this.input.value)
        terms.pop()
        terms.push(option.textContent || '', '')
        this.input.value = terms.join(', ')

        this.close()
    }

    private onKeyDown = (event: KeyboardEvent) => {
        if (event.isComposing || !this.isOpen) return

        const count = this.options.length
        const hasActive = this.activeIndex >= 0

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault()
                this.setActive((this.activeIndex + 1) % count)
                break
            case 'ArrowUp':
                event.preventDefault()
                this.setActive(hasActive ? this.activeIndex - 1 : count - 1)
                break
            // With an active option, pick it and stay in the field to type the next value.
            // Without one, Enter submits the form and Tab moves on, as usual.
            case 'Enter':
            case 'Tab':
                if (!hasActive) {
                    this.close()
                    break
                }
                event.preventDefault()
                this.select(this.activeIndex)
                break
            case 'Escape':
                event.preventDefault()
                event.stopPropagation()
                this.close()
                break
        }
    }

    private onListMouseDown = (event: MouseEvent) => {
        event.preventDefault()

        const option = (event.target as Element).closest<HTMLElement>(
            '[role="option"]',
        )
        if (option) this.select(this.options.indexOf(option))
    }

    private onBlur = () => {
        this.cancelPendingSearch()
        this.close()
    }

    private split(value: string) {
        return value.split(/,\s*/)
    }

    private lastTerm(value: string) {
        return this.split(value).pop() || ''
    }
}
