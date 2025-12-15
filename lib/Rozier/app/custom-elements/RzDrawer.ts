import { getItemsByIds } from '~/api/DrawerApi'

interface RzDrawerItem {
    color?: string
    displayable?: string
    editItem?: string
    id: number
    published?: boolean
    thumbnail?: object // TODO: Define a proper type for the Document
}

export class RzDrawer extends HTMLElement {
    fileUploadIsVisible: boolean = false
    fileUpload: HTMLElement | null = null

    constructor() {
        super()

        this.onCommand = this.onCommand.bind(this)
        this.onAddDrawerItem = this.onAddDrawerItem.bind(this)
    }

    connectedCallback() {
        this.fileUpload = this.querySelector('rz-file-upload')

        this.addEventListener('command', this.onCommand)
        document.addEventListener('add-drawer-item', this.onAddDrawerItem)

        this.initItems()
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
        document.removeEventListener('add-drawer-item', this.onAddDrawerItem)
    }

    showFileUpload() {
        if (this.fileUploadIsVisible) {
            return
        }

        this.fileUploadIsVisible = true

        if (!this.fileUpload) {
            return
        }

        this.fileUpload.removeAttribute('hidden')

        this.updateFileUploadControls()
    }

    hideFileUpload() {
        if (!this.fileUploadIsVisible) {
            return
        }

        this.fileUploadIsVisible = false

        if (!this.fileUpload) {
            return
        }

        this.fileUpload.setAttribute('hidden', '')

        this.updateFileUploadControls()
    }

    updateFileUploadControls() {
        if (!this.fileUpload) {
            return
        }

        const controls = document.querySelectorAll(
            `[aria-controls="${this.fileUpload.id}"]`,
        )

        controls.forEach((button) => {
            button.setAttribute(
                'aria-expanded',
                this.fileUploadIsVisible ? 'true' : 'false',
            )
        })
    }

    toggleFileUpload() {
        if (this.fileUploadIsVisible) {
            this.hideFileUpload()
        } else {
            this.showFileUpload()
        }
    }

    openExplorer() {
        document.dispatchEvent(
            new CustomEvent('show-explorer', {
                detail: {
                    id: this.getAttribute('id'),
                    acceptEntity: this.getAttribute('accept-entity'),
                },
            }),
        )
    }

    appendItem(item: RzDrawerItem) {
        const list = this.querySelector('[data-list]')

        if (!list) {
            return
        }

        const element = document.createElement('div')
        element.classList.add('rz-card')
        element.textContent = item.displayable

        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = this.getAttribute('input-name') || 'drawer-items[]'
        input.value = item.id.toString()
        element.appendChild(input)

        list.appendChild(element)
    }

    async initItems() {
        const items = JSON.parse(this.getAttribute('items') || '[]')
        const entityType = this.getAttribute('accept-entity') || ''

        if (!Array.isArray(items) || items.length === 0 || !entityType) {
            return
        }

        const response: { items?: RzDrawerItem[] } = await getItemsByIds(
            entityType,
            items,
        ).catch((error) => {
            console.error('Error fetching drawer items', error)
        })

        if (response && response.items) {
            response.items.forEach((item) => {
                this.appendItem(item)
            })
        }
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--open-explorer':
                this.openExplorer()
                break
            case '--toggle-file-upload':
                this.toggleFileUpload()
                break
        }
    }

    onAddDrawerItem(event: CustomEvent) {
        const drawerId = event.detail.drawerId || ''

        if (!drawerId || drawerId !== this.getAttribute('id')) {
            return
        }

        const item = event.detail.item

        if (item) {
            this.appendItem(item)
        }
    }
}
