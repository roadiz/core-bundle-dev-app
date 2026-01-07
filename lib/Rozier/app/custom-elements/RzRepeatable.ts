export class RzRepeatable extends HTMLElement {
    list: HTMLElement | null = null
    itemTemplate: HTMLTemplateElement | null = null
    itemClass = 'rz-repeatable__item'

    // With example values
    inputIndexPlaceholder = '__name__'
    inputBaseName = 'source[key]'
    idBaseName = 'source_key'

    constructor() {
        super()

        this.addItem = this.addItem.bind(this)
        this.removeItem = this.removeItem.bind(this)
        this.moveDownItem = this.moveDownItem.bind(this)
        this.moveUpItem = this.moveUpItem.bind(this)
    }

    getClosestItem(element: HTMLElement) {
        return element.closest(`.${this.itemClass}`) as HTMLElement
    }

    moveDownItem(event: Event) {
        event.preventDefault()
        const currentItem = this.getClosestItem(event.target as HTMLElement)
        const nextElement = currentItem?.nextElementSibling

        if (currentItem && nextElement) {
            nextElement.after(currentItem)

            this.updateAllInputAttributes()
        }
    }

    moveUpItem(event: Event) {
        event.preventDefault()
        const currentItem = this.getClosestItem(event.target as HTMLElement)
        const previousItem = currentItem?.previousElementSibling

        if (currentItem && previousItem) {
            previousItem.before(currentItem)
            this.updateAllInputAttributes()
        }
    }

    removeItem(event: Event) {
        event.preventDefault()
        const parentElement = this.getClosestItem(event.target as HTMLElement)
        // TODO: Need to remove all inner buttons listeners ?
        parentElement?.remove()

        // item name and id need to be updated only if not the last item removed
        const isLastItem = !parentElement?.nextElementSibling
        if (!isLastItem) {
            this.updateAllInputAttributes()
        }
    }

    addItem(event: Event) {
        event.preventDefault()
        const newItem = this.itemTemplate?.content.firstElementChild?.cloneNode(
            true,
        ) as HTMLElement

        const targetItem = this.getClosestItem(event.target as HTMLElement)
        if (targetItem) {
            targetItem.after(newItem)
        } else {
            this.list?.prepend(newItem)
        }

        this.initButtonsListeners(newItem)
        this.updateAllInputAttributes()
    }

    private escapeRegExp(str: string): string {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    }

    updateAllInputAttributes() {
        const items = this.list?.querySelectorAll(`.${this.itemClass}`)
        if (!items || !items.length) return

        const indexEscaped = this.escapeRegExp(this.inputIndexPlaceholder)

        const inputBaseNameEscaped = this.escapeRegExp(this.inputBaseName)
        const nameRegex = new RegExp(
            `^(${inputBaseNameEscaped})\\[(?<id>${indexEscaped}|\\d+)\\](.*)$`,
        )

        const idBaseNameEscaped = this.escapeRegExp(this.idBaseName)
        const idRegex = new RegExp(
            `^(${idBaseNameEscaped})(?<id>_{1,3}${indexEscaped}_{1,3}|_{1,3}\\d+_{1,3})(.*)$`,
        )

        items.forEach((item, itemIndex) => {
            const inputs = item.querySelectorAll(
                `input[name^="${this.inputBaseName}"], select[name^="${this.inputBaseName}"], textarea[name^="${this.inputBaseName}"]`,
            )

            inputs.forEach((input) => {
                const name = input.getAttribute('name')
                const nameMatch = name?.match(nameRegex)

                if (!nameMatch?.groups?.id) {
                    console.warn("Can't extract id/index from input name", name)
                    return
                }
                const newName = `${nameMatch[1]}[${itemIndex}]${nameMatch[3]}`
                input.setAttribute('name', newName)

                const id = input.getAttribute('id')
                const label = item.querySelector(`label[for="${id}"]`)
                const idMatch = id?.match(idRegex)
                if (label) {
                    const newId = [idMatch?.[1], itemIndex, idMatch?.[3]]
                        .filter((v) => !!v || typeof v === 'number')
                        .join('_')
                    input.setAttribute('id', newId)
                    label.setAttribute('for', newId)
                }
            })
        })
    }

    initButtonsListeners(context: HTMLElement) {
        const addButtons = context.querySelectorAll('[data-add]')
        addButtons.forEach((button) => {
            button.addEventListener('click', this.addItem)
        })

        const removeButtons = context.querySelectorAll('[data-remove]')
        removeButtons.forEach((button) => {
            button.addEventListener('click', this.removeItem)
        })

        const moveUpButtons = context.querySelectorAll('[data-move-up]')
        moveUpButtons.forEach((button) => {
            button.addEventListener('click', this.moveUpItem)
        })

        const moveDownButtons = context.querySelectorAll('[data-move-down]')
        moveDownButtons.forEach((button) => {
            button.addEventListener('click', this.moveDownItem)
        })
    }

    connectedCallback() {
        this.list = this.querySelector('[data-list]')
        this.itemTemplate = this.querySelector('template[data-item]')

        this.inputIndexPlaceholder =
            this.getAttribute('input-index-placeholder') || ''
        this.inputBaseName = this.getAttribute('input-base-name') || ''
        this.idBaseName = this.getAttribute('id-base-name') || ''
        if (this.getAttribute('item-class')) {
            this.itemClass = this.getAttribute('item-class')
        }

        this.initButtonsListeners(this)
    }

    disconnectedCallback() {
        const addButtons = this.querySelectorAll('[data-add]')
        addButtons.forEach((button) => {
            button.removeEventListener('click', this.addItem)
        })

        const removeButtons = this.querySelectorAll('[data-remove]')
        removeButtons.forEach((button) => {
            button.removeEventListener('click', this.removeItem)
        })

        const moveUpButtons = this.querySelectorAll('[data-move-up]')
        moveUpButtons.forEach((button) => {
            button.removeEventListener('click', this.moveUpItem)
        })

        const moveDownButtons = this.querySelectorAll('[data-move-down]')
        moveDownButtons.forEach((button) => {
            button.removeEventListener('click', this.moveDownItem)
        })
    }
}
