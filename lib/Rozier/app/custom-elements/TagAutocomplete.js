export default class TagAutocomplete extends HTMLElement {
    constructor() {
        super()
        this.debounce = null
        this.input = this.querySelector('.rz-tag-autocomplete')

        this.onInput = this.onInput.bind(this)
        this.onKeyDown = this.onKeyDown.bind(this)
        this.resize = this.resize.bind(this)
    }

    get value() {
        if (this.input) {
            return this.input.value
        }
        return ''
    }

    get searchPath() {
        return this.getAttribute('data-search-path')
    }

    get csrfToken() {
        return this.getAttribute('data-csrf-token')
    }

    connectedCallback() {
        if (!this.input) {
            return
        }

        // Don't navigate away from the field on tab when selecting an item
        this.input.setAttribute('autocomplete', 'off')
        this.input.addEventListener('keydown', this.onKeyDown)
        this.input.addEventListener('input', this.onInput)
        window.addEventListener('resize', this.resize)
    }

    disconnectedCallback() {
        if (!this.input) {
            return
        }

        this.input.removeEventListener('keydown', this.onKeyDown)
        this.input.removeEventListener('input', this.onInput)
        window.removeEventListener('resize', this.resize)
    }

    onKeyDown(event) {
        if (event.keyCode === 9 && this.input.dataset.menuActive === 'true') {
            event.preventDefault()
        }
    }

    async onInput(event) {
        const term = this.extractLast(this.value)

        if (term.length < 2) {
            this.removeAutocompleteMenu()
            return
        }

        const postData = {
            _action: 'tagAutocomplete',
            _token: this.csrfToken,
            search: this.extractLast(term),
        }

        const url = this.searchPath + '?' + new URLSearchParams(postData)

        /**
         * Always debounce fetch calls triggered by user input
         */
        if (this.debounce) {
            window.clearTimeout(this.debounce)
            this.debounce = null
        }
        this.debounce = window.setTimeout(async () => {
            const res = await fetch(url, {
                headers: {
                    // Required to prevent using this route as referer when login again
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            this.showAutocompleteMenu(await res.json())
        }, 300)
    }

    // Create a list with the suggestions and show it under the input
    showAutocompleteMenu(suggestions) {
        this.removeAutocompleteMenu()

        const menu = document.createElement('ul')
        menu.className = 'ui-menu ui-widget ui-widget-content ui-autocomplete ui-front'

        suggestions.forEach((suggestion) => {
            const item = document.createElement('li')
            item.className = 'ui-menu-item'
            item.textContent = suggestion

            item.addEventListener('mousedown', (event) => {
                event.preventDefault()

                let terms = this.split(this.input.value)
                terms.pop()
                terms.push(suggestion)
                terms.push('')
                this.input.value = terms.join(', ')

                this.removeAutocompleteMenu()
            })

            menu.appendChild(item)
        })

        this.appendChild(menu)
        this.input.dataset.menuActive = 'true'
        this.resize()

        document.addEventListener('click', this.handleOutsideClick)
    }

    resize() {
        const menu = this.querySelector('.ui-autocomplete')
        if (menu) {
            // Reposition the menu if the input has been resized
            const rect = this.input.getBoundingClientRect()
            menu.style.left = `${this.input.offsetLeft}px`
            menu.style.top = `${this.input.offsetTop + this.input.offsetHeight}px`
        }
    }

    // Remove the autocomplete menu if it exists
    removeAutocompleteMenu() {
        const existing = this.querySelector('.ui-autocomplete')
        if (existing) {
            existing.remove()
            document.removeEventListener('click', this.handleOutsideClick)
        }

        if (this.input) {
            this.input.dataset.menuActive = 'false'
        }
    }

    handleOutsideClick = (e) => {
        if (!e.target.closest('.ui-autocomplete')) {
            this.removeAutocompleteMenu()
        }
    }

    unbind() {}

    split(val) {
        return val.split(/,\s*/)
    }

    extractLast(term) {
        return this.split(term).pop()
    }
}
