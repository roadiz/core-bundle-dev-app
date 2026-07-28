export class RzBulkSection extends HTMLElement {
    selectAllInputElements: HTMLInputElement[] = []
    itemInputElements: HTMLInputElement[] = []

    selectAllButtonElements: HTMLElement[] = []
    deselectAllButtonElements: HTMLElement[] = []

    formInputElements: HTMLInputElement[] = []

    bulkSelection: string[] = []

    constructor() {
        super()

        this.toggleSelection = this.toggleSelection.bind(this)
        this.selectAll = this.selectAll.bind(this)
        this.deselectAll = this.deselectAll.bind(this)
        this.onBulkInputChange = this.onBulkInputChange.bind(this)
    }

    toggleActionVisibility(visible: boolean) {
        const actionElement = this.querySelector('rz-bulk-actions')
        const className = actionElement?.getAttribute('visibility-class') || ''

        const methodName = visible ? 'add' : 'remove'
        actionElement?.classList[methodName](className)
    }

    toggleSelectedClass(element: HTMLElement, add: boolean) {
        const selectedClass = element.getAttribute('selected-class')
        if (!selectedClass) return

        const method = add ? 'add' : 'remove'
        element.classList[method](selectedClass)
    }

    onBulkInputChange(event: Event) {
        const element = event.currentTarget as HTMLInputElement
        if (element.checked) {
            this.bulkSelection.push(element.value)
        } else {
            this.bulkSelection.splice(
                this.bulkSelection.indexOf(element.value),
                1,
            )
        }

        if (this.bulkSelection.length > 0) {
            this.toggleActionVisibility(true)

            this.formInputElements.forEach((inputElement) => {
                inputElement.value = JSON.stringify(this.bulkSelection)
            })
        } else {
            this.deselectAll()
        }
    }

    toggleSelection() {
        if (this.bulkSelection?.length) {
            this.deselectAll()
        } else {
            this.selectAll()
        }
    }

    selectAll() {
        this.toggleActionVisibility(true)

        this.selectAllInputElements.forEach((el) => {
            el.checked = true
        })

        this.itemInputElements.forEach((element) => {
            element.checked = true

            if (!this.bulkSelection.includes(element.value)) {
                this.bulkSelection.push(element.value)
            }
        })

        this.formInputElements.forEach((inputElement) => {
            inputElement.value = JSON.stringify(this.bulkSelection)
        })

        this.selectAllButtonElements.forEach((element) => {
            this.toggleSelectedClass(element, true)
        })
    }

    deselectAll() {
        this.toggleActionVisibility(false)

        this.selectAllInputElements.forEach((el) => {
            el.checked = false
        })

        this.formInputElements.forEach((element) => {
            element.value = ''
        })

        this.itemInputElements.forEach((element) => {
            element.checked = false
            this.bulkSelection.splice(0, this.bulkSelection.length)
        })

        this.selectAllButtonElements.forEach((element) => {
            this.toggleSelectedClass(element, false)
        })
    }

    setQuerySelectorAll<T extends HTMLElement>(
        selector: string,
        callback?: (el: T) => void,
    ) {
        const elements = Array.from(this.querySelectorAll<T>(selector))
        if (elements?.length && callback) {
            elements.forEach(callback)
        }
        return elements
    }

    connectedCallback() {
        this.formInputElements = this.setQuerySelectorAll(
            'input.bulk-form-value',
        )
        this.selectAllButtonElements = this.setQuerySelectorAll(
            '[data-bulk-select-all-button]',
            (el) => {
                el.addEventListener('click', this.selectAll)
            },
        )

        this.deselectAllButtonElements = this.setQuerySelectorAll(
            '[data-bulk-deselect-all-button]',
            (el) => {
                el.addEventListener('click', this.deselectAll)
            },
        )

        this.selectAllInputElements = this.setQuerySelectorAll(
            'input[name="bulk-selection-all"]',
            (el) => {
                el.addEventListener('change', this.toggleSelection)
            },
        )

        this.itemInputElements = this.setQuerySelectorAll(
            'input[name="bulk-selection[]"]',
            (el) => {
                el.addEventListener('change', this.onBulkInputChange)
            },
        )
    }

    disconnectedCallback() {
        this.setQuerySelectorAll('[data-bulk-select-all-button]', (el) => {
            el.removeEventListener('click', this.selectAll)
        })

        this.setQuerySelectorAll('[data-bulk-deselect-all-button]', (el) => {
            el.removeEventListener('click', this.deselectAll)
        })

        this.setQuerySelectorAll('input[name="bulk-selection-all"]', (el) => {
            el.removeEventListener('change', this.toggleSelection)
        })

        this.setQuerySelectorAll('input[name="bulk-selection[]"]', (el) => {
            el.removeEventListener('change', this.onBulkInputChange)
        })
    }
}
