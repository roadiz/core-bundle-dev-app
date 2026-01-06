// import '~/vendor/jquery.collection'
// import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

export class RzRepeatable extends HTMLElement {
    list: HTMLElement | null = null
    prototypeTemplate: HTMLTemplateElement | null = null
    itemTemplate: HTMLTemplateElement | null = null
    insertZoneTemplate: HTMLTemplateElement | null = null
    itemClass = 'rz-repeatable__item'

    prototypeName = '__name__'
    namePrefix = 'source[key]'

    constructor() {
        super()
        this.list = this.querySelector('[data-list]')
        this.prototypeTemplate = this.querySelector('template[data-prototype]')
        this.itemTemplate = this.querySelector('template[data-item]')
        this.insertZoneTemplate = this.querySelector(
            'template[data-insert-zone]',
        )
        this.prototypeName = this.getAttribute('prototype-name') || ''
        this.namePrefix = this.getAttribute('name-prefix') || ''

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
        const parentElement = this.getClosetestItem(event.target as HTMLElement)
        // TODO: re order item placement logic
        this.updateInputs()
    }

    moveUpItem(event: Event) {
        event.preventDefault()
        const parentElement = this.getClosetestItem(event.target as HTMLElement)
        // TODO: re order item placement logic
        this.updateInputs()
    }

    removeItem(event: Event) {
        event.preventDefault()
        const parentElement = this.getClosetestItem(event.target as HTMLElement)

        console.log('removing item', parentElement)
        parentElement?.remove()

        // item name and id need to be updated only if not the last item removed
        const isLastItem = parentElement?.nextElementSibling === null
        if (!isLastItem) {
            this.updateInputs()
        }
    }

    addItem(event: Event) {
        event.preventDefault()
        const item = this.itemTemplate?.content.firstElementChild?.cloneNode(
            true,
        ) as HTMLElement
        console.log('item to add', item)
        // TODO: Set index depending on where addButton is located
        const items = this.list?.querySelectorAll(`.${this.itemClass}`)
        const itemIndex = items ? items.length : 0
        item.setAttribute?.('data-item-index', String(itemIndex + 1))

        const bodyElement = item.querySelector('[data-form]')
        bodyElement.appendChild(this.prototypeTemplate?.content.cloneNode(true))

        item.appendChild(this.insertZoneTemplate?.content.cloneNode(true))

        this.initButtonsListeners(item)
        this.list?.appendChild(item)
        this.updateInputs()
    }

    updateInputs() {
        const items = this.list?.querySelectorAll(`.${this.itemClass}`)
        console.log('updating inputs for items', items)

        if (!items.length) return

        items?.forEach((item, itemIndex) => {
            const inputs = item.querySelectorAll(
                `input[name^="${this.namePrefix}"], select[name^="${this.namePrefix}"], textarea[name^="${this.namePrefix}"]`,
            )

            inputs.forEach((input) => {
                const name = input.getAttribute('name')
                if (name) {
                    const newName = name.replace(
                        new RegExp(this.prototypeName, 'g'),
                        String(itemIndex),
                    )

                    input.setAttribute('name', newName)
                }

                const id = input.getAttribute('id')
                if (id) {
                    const newId = id.replace(
                        new RegExp(this.prototypeName, 'g'),
                        '_' + String(itemIndex) + '_',
                    )
                    input.setAttribute('id', newId)
                    const label = item.querySelector(`label[for="${id}"]`)
                    if (label) {
                        label.setAttribute('for', newId)
                    }
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
        const items = this.list?.querySelectorAll(`.${this.itemClass}`)
        if (items.length) {
            items.forEach((item, index) => {
                item.setAttribute('data-item-index', String(index + 1))
            })
        }
        this.initButtonsListeners(this)
    }

    disconnectedCallback() {}
}
