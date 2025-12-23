export class RzFiltersBar extends HTMLElement {
    constructor() {
        super()

        this.submitOnChange = this.submitOnChange.bind(this)
        this.onLayoutChange = this.onLayoutChange.bind(this)
    }

    submitOnChange(event: Event) {
        const el = event.currentTarget as HTMLElement

        const form = el.closest('form')
        if (form) form.requestSubmit()
    }

    onLayoutChange(event: Event) {
        const el = event.currentTarget as HTMLElement
        const buttons = this.querySelectorAll('[data-layout-button]')

        buttons.forEach((button) => {
            const selectedClass =
                button?.getAttribute('data-layout-button-selected-class') ||
                'rz-button--selected'
            const input = button?.querySelector('input[name="layout"]')
            if (el === input) {
                button.classList.add(selectedClass)
            } else {
                button.classList.remove(selectedClass)
            }
        })

        const form = el.closest('form')
        if (form) form.requestSubmit()
    }

    setQuerySelectorAll<T extends HTMLElement>(
        selector: string,
        callback: (el: T) => void,
    ) {
        const elements = Array.from(this.querySelectorAll<T>(selector))
        if (!elements.length) return

        elements.forEach((selectElement) => {
            callback(selectElement)
        })
    }

    connectedCallback() {
        this.setQuerySelectorAll('input[name="layout"]', (el) => {
            el.addEventListener('change', this.onLayoutChange)
        })
        this.setQuerySelectorAll('[data-submit-on-change]', (el) => {
            el.addEventListener('change', this.submitOnChange)
        })
    }

    disconnectedCallback() {
        this.setQuerySelectorAll('input[name="layout"]', (el) => {
            el.removeEventListener('change', this.onLayoutChange)
        })
        this.setQuerySelectorAll('[data-submit-on-change]', (el) => {
            el.removeEventListener('change', this.submitOnChange)
        })
    }
}
