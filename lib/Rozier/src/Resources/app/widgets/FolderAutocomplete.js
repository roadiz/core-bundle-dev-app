export default class FolderAutocomplete {
    constructor() {
        const _this = this

        const input = document.querySelector('.rz-folder-autocomplete')

        if (!input) {
            return
        }

        // Don't navigate away from the field on tab when selecting an item
        input.addEventListener('keydown', function (event) {
            if (event.keyCode === 9 && input.dataset.menuActive === 'true') {
                event.preventDefault()
            }
        })

        // Auto-complete logic
        input.addEventListener('input', async function () {
            const term = _this.extractLast(this.value)

            if (term.length < 2) {
                _this.removeAutocompleteMenu()
                return
            }

            const postData = {
                _action: 'folderAutocomplete',
                _token: window.RozierConfig.ajaxToken,
                search: _this.extractLast(term),
            }

            const url = window.RozierConfig.routes.foldersAjaxSearch + '?' + new URLSearchParams(postData)

            const res = await fetch(url)
            const data = await res.json()

            _this.showAutocompleteMenu(this, data)
        })
    }

    // Create a list with the suggestions and show it under the input
    showAutocompleteMenu(input, suggestions) {
        this.removeAutocompleteMenu()

        const menu = document.createElement('ul')
        // class name of jquery ui autocomplete
        // TODO: create custom class for autocomplete menu
        menu.className = 'ui-menu ui-widget ui-widget-content ui-autocomplete ui-front'
        menu.style.position = 'absolute'
        menu.style.zIndex = '1000'

        suggestions.forEach((suggestion) => {
            const item = document.createElement('li')
            item.className = 'ui-menu-item'
            item.textContent = suggestion

            item.addEventListener('mousedown', (event) => {
                event.preventDefault()

                let terms = this.split(input.value)
                terms.pop()
                terms.push(suggestion)
                terms.push('')
                input.value = terms.join(', ')

                this.removeAutocompleteMenu()
            })

            menu.appendChild(item)
        })

        input.parentNode.appendChild(menu)
        input.dataset.menuActive = 'true'

        // Positionner le menu sous l'input
        const rect = input.getBoundingClientRect()
        menu.style.left = `${input.offsetLeft}px`
        menu.style.top = `${input.offsetTop + input.offsetHeight}px`

        document.addEventListener('click', this.handleOutsideClick)
    }

    // Remove the autocomplete menu if it exists
    removeAutocompleteMenu() {
        const existing = document.querySelector('.ui-autocomplete')
        if (existing) {
            existing.remove()
            document.removeEventListener('click', this.handleOutsideClick)
        }

        const input = document.querySelector('.rz-folder-autocomplete')
        if (input) {
            input.dataset.menuActive = 'false'
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
