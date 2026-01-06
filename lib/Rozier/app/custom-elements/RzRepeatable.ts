export class RzRepeatable extends HTMLElement {
    list: HTMLElement | null = null
    itemTemplate: HTMLTemplateElement | null = null
    itemClass = 'rz-repeatable__item'

    // With example values
    prototypeName = '__name__'
    namePrefix = 'source[key]'
    idPrefix = 'source_key'

    constructor() {
        super()
        this.list = this.querySelector('[data-list]')
        this.itemTemplate = this.querySelector('template[data-item]')

        this.prototypeName = this.getAttribute('prototype-name') || ''
        this.namePrefix = this.getAttribute('name-prefix') || ''
        this.idPrefix = this.getAttribute('id-prefix') || ''
        if (this.getAttribute('item-class')) {
            this.itemClass = this.getAttribute('item-class')
        }

        this.addItem = this.addItem.bind(this)
        this.removeItem = this.removeItem.bind(this)
        this.moveDownItem = this.moveDownItem.bind(this)
        this.moveUpItem = this.moveUpItem.bind(this)
    }

    getClosetestItem(element: HTMLElement) {
        return element.closest(`.${this.itemClass}`) as HTMLElement
    }

    moveDownItem(event: Event) {
        event.preventDefault()
        const currentItem = this.getClosetestItem(event.target as HTMLElement)
        const nextElement = currentItem?.nextElementSibling

        if (currentItem && nextElement) {
            nextElement.after(currentItem)

            this.updateAllInputAttributes()
        }
    }

    moveUpItem(event: Event) {
        event.preventDefault()
        const currentItem = this.getClosetestItem(event.target as HTMLElement)
        const previousItem = currentItem?.previousElementSibling

        if (currentItem && previousItem) {
            previousItem.before(currentItem)
            this.updateAllInputAttributes()
        }
    }

    removeItem(event: Event) {
        event.preventDefault()
        const parentElement = this.getClosetestItem(event.target as HTMLElement)
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

        const targetItem = this.getClosetestItem(event.target as HTMLElement)
        if (targetItem) {
            targetItem.after(newItem)
        } else {
            this.list.prepend(newItem)
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

        const escapedIdPrefix = this.escapeRegExp(this.idPrefix)
        const escapedPrefix = this.escapeRegExp(this.namePrefix)
        const escapedProtoName = this.escapeRegExp(this.prototypeName)

        const nameRegex = new RegExp(
            `^(${escapedPrefix})\\[(?<id>${escapedProtoName}|\\d+)\\](.*)$`,
        )
        const idRegex = new RegExp(
            `^(${escapedIdPrefix})(?<id>_{1,3}${escapedProtoName}_{1,3}|_{1,3}\\d+_{1,3})(.*)$`,
        )

        items.forEach((item, itemIndex) => {
            const inputs = item.querySelectorAll(
                `input[name^="${this.namePrefix}"], select[name^="${this.namePrefix}"], textarea[name^="${this.namePrefix}"]`,
            )

            inputs.forEach((input) => {
                const name = input.getAttribute('name')
                const nameMatch = name?.match(nameRegex)

                if (!nameMatch.groups?.id) {
                    console.warn('No newId found for name', name)
                    return
                }
                const newName = `${nameMatch[1]}[${itemIndex}]${nameMatch[3]}`
                input.setAttribute('name', newName)

                const id = input.getAttribute('id')
                const label = item.querySelector(`label[for="${id}"]`)
                const idMatch = id?.match(idRegex)
                console.log('id', id, idMatch)
                if (label) {
                    const newId = [idMatch?.[1], itemIndex, idMatch?.[3]]
                        .filter((v) => v)
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
