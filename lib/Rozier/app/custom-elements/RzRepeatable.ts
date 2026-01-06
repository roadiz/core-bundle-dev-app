// import '~/vendor/jquery.collection'
// import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

export class RzRepeatable extends HTMLElement {
    list: HTMLElement | null = null
    prototypeTemplate: HTMLTemplateElement | null = null
    itemTemplate: HTMLTemplateElement | null = null
    insertZoneTemplate: HTMLTemplateElement | null = null
    itemLength = 0
    itemClass = 'rz-repeatable__item'

    prototypeName = ''
    namePrefix = ''

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

    moveDownItem(event: Event) {
        event.preventDefault()
        // TODO: re order item placement logic
        this.updateInputs()
    }

    moveUpItem(event: Event) {
        event.preventDefault()
        // TODO: re order item placement logic
        this.updateInputs()
    }

    removeItem(event: Event) {
        event.preventDefault()
        const parentElement = (event.currentTarget as HTMLElement).closest(
            `.${this.itemClass}`,
        )

        console.log('removing item', parentElement)
        parentElement?.remove()
        this.itemLength =
            this.list?.querySelectorAll(`.${this.itemClass}`).length || 0
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

    addItem(event: Event) {
        event.preventDefault()
        const item = this.itemTemplate?.content.firstElementChild?.cloneNode(
            true,
        ) as HTMLElement
        console.log('item to add', item)
        // TODO: Set index depending on where addButton is located
        this.itemLength++
        item.setAttribute?.('data-item-index', String(this.itemLength))

        const bodyElement = item.querySelector('[data-form]')
        bodyElement.appendChild(this.prototypeTemplate?.content.cloneNode(true))

        item.appendChild(this.insertZoneTemplate?.content.cloneNode(true))

        this.initButtonsListeners(item)
        this.list?.appendChild(item)
        this.updateInputs()
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

    disconnectedCallback() {}
}
