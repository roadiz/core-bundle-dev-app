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

        this.onCommand = this.onCommand.bind(this)
    }

    // Avoid selecting button in nested repeatables
    private filterInnerNestedItems(selector: string) {
        const elements = Array.from(this.querySelectorAll(selector))
        return elements.filter((el) => {
            return el.closest('rz-repeatable') === this
        })
    }

    moveItem(element: HTMLElement, direction: number) {
        if (direction === -1 && element.previousElementSibling) {
            element.previousElementSibling.before(element)
        } else if (direction === 1 && element.nextElementSibling) {
            element.nextElementSibling.after(element)
        }

        this.updateAllInputAttributes()
        this.updateMoveButtonsState()
    }

    removeItem(item: HTMLElement | undefined) {
        item?.remove()

        const isLastItem = !item?.nextElementSibling
        if (!isLastItem) {
            this.updateAllInputAttributes()
            this.updateMoveButtonsState()
        }
    }

    addItem(index: string | null) {
        const itemToDuplicate = this.itemTemplate.content.firstElementChild
        if (!itemToDuplicate) return

        const newItem = document.importNode(itemToDuplicate, true)
        if (index) {
            const referenceNode = this.list?.children.item(parseInt(index, 10))
            this.list?.insertBefore(newItem, referenceNode || null)
        } else {
            this.list?.appendChild(newItem)
        }

        this.updateAllInputAttributes()
        this.updateMoveButtonsState()
    }

    onCommand(event: CommandEvent) {
        const button = event.source
        const item = button.closest(`.${this.itemClass}`) as HTMLElement

        switch (event.command) {
            case '--add':
                this.addItem(button.getAttribute('data-insert-index'))
                break
            case '--remove':
                this.removeItem(item)
                break
            case '--move-up':
                this.moveItem(item, -1)
                break
            case '--move-down':
                this.moveItem(item, 1)
                break
        }
    }

    private escapeRegExp(str: string): string {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    }

    get itemElements() {
        return Array.from(
            this.list?.querySelectorAll(`.${this.itemClass}`) || [],
        ).filter((el) => {
            // Avoid selecting items from nested repeatables
            return el.closest('rz-repeatable') === this
        })
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
            const inputs = Array.from(
                item.querySelectorAll(
                    `input[name^="${this.inputBaseName}"], select[name^="${this.inputBaseName}"], textarea[name^="${this.inputBaseName}"]`,
                ),
            ).filter((el) => {
                // Avoid selecting inputs from nested repeatables
                return el.closest('rz-repeatable') === this
            })

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

    updateMoveButtonsState() {
        const buttons = Array.from(
            this.querySelectorAll(
                `button[command="--move-up"], button[command="--move-down"]`,
            ),
        ).filter((el) => {
            return el.closest('rz-repeatable') === this
        })

        buttons.forEach((button) => {
            const item = button.closest(`.${this.itemClass}`)
            if (!item) return

            const isFirstItem = !item.previousElementSibling
            const isLastItem = !item.nextElementSibling

            if (button.getAttribute('command') === '--move-up' && isFirstItem) {
                button.setAttribute('disabled', 'true')
            } else if (
                button.getAttribute('command') === '--move-down' &&
                isLastItem
            ) {
                button.setAttribute('disabled', 'true')
            } else {
                button.removeAttribute('disabled')
            }
        })
    }

    connectedCallback() {
        this.list = this.querySelector('[data-list]')
        this.itemTemplate = this.querySelector('template[data-item]')

        this.inputIndexPlaceholder =
            this.getAttribute('input-index-placeholder') || ''
        this.inputBaseName = this.getAttribute('input-base-name') || ''
        this.idBaseName = this.getAttribute('id-base-name') || ''
        if (this.hasAttribute('item-class')) {
            this.itemClass = this.getAttribute('item-class')
        }

        this.updateMoveButtonsState()
        this.addEventListener('command', this.onCommand)
    }

    destroyCommands() {
        this.removeEventListener('command', this.onCommand)
    }
}
