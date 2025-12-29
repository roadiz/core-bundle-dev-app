import { getItemsByIds } from '~/api/DrawerApi'
import { RzButtonOptions } from '~/utils/component-renderer/rzButton'
import {
    RzCardOptions,
    rzCardRenderer,
} from '~/utils/component-renderer/rzCard'

interface RzDrawerItem {
    classname?: string
    color?: string
    displayable?: string
    document?: number
    editImageHeight?: number
    editImageUrl?: string
    editImageWidth?: number
    editItem?: string
    embedPlatform?: string | null
    hasThumbnail?: boolean
    hotspot?: { x: number; y: number }
    icon?: string
    id?: number
    imageCropAlignment?: string
    isEmbed?: boolean
    isImage?: boolean
    isPdf?: boolean
    isPrivate?: boolean
    isSvg?: boolean
    isVideo?: boolean
    isWebp?: boolean
    originalHotspot?: { x: number; y: number } | null
    previewHtml?: string
    processable?: boolean
    published?: boolean
    relativePath?: string
    shortMimeType?: string
    shortType?: string
    thumbnail?: string | null
    thumbnail80?: string
}

interface DocumentItemAttribute {
    document?: number
    id?: number
    originalHotspot?: { x: number; y: number } | null
    imageCropAlignment?: string
}

type ItemAttribute = number | DocumentItemAttribute

export class RzDrawer extends HTMLElement {
    fileUploadIsVisible: boolean = false
    fileUpload: HTMLElement | null = null
    items: RzDrawerItem[] = []
    listElement: HTMLElement | null = null
    drawerName: string = ''
    itemElements: WeakMap<RzDrawerItem, HTMLElement> = new WeakMap()

    constructor() {
        super()

        this.onCommand = this.onCommand.bind(this)
        this.onAddDrawerItem = this.onAddDrawerItem.bind(this)
    }

    connectedCallback() {
        this.fileUpload = this.querySelector('rz-file-upload')
        this.listElement = this.querySelector('[data-list]')
        this.drawerName = this.getAttribute('name') || 'drawer-items[]'

        // Listen for commands from external buttons
        this.addEventListener('command', this.onCommand)
        // Listen for explorer add item events
        document.addEventListener('add-drawer-item', this.onAddDrawerItem)

        this.initItems()
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
        document.removeEventListener('add-drawer-item', this.onAddDrawerItem)

        // Clear cached references
        this.listElement = null
        this.itemElements = new WeakMap()
    }

    // ATTRIBUTES
    get acceptEntity(): string {
        return this.getAttribute('accept-entity') || ''
    }

    // COMMANDS
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

    // FILE UPLOAD
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

    // EXPLORER
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

    // ITEMS
    appendItem(item: RzDrawerItem) {
        if (!this.listElement) {
            return
        }

        this.items.push(item)
        const itemIndex = this.items.length - 1

        const element = this.createItemElement(item, itemIndex)
        this.itemElements.set(item, element)
        this.listElement.appendChild(element)
    }

    createItemElement(item: RzDrawerItem, index: number): HTMLElement {
        const buttons: RzButtonOptions[] = [
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                emphasis: 'tertiary',
                color: 'danger',
                attributes: {
                    type: 'button', // do not submit form
                },
                on: {
                    click: () => {
                        this.removeItem(item)
                    },
                },
            },
        ]

        // Edit link
        if (item.editItem) {
            const href = item.editItem + '?referer=' + window.location.pathname

            // Image
            if (item.isImage) {
                buttons.unshift({
                    tag: 'a',
                    iconClass: 'rz-icon-ri--equalizer-3-line',
                    emphasis: 'primary',
                    attributes: {
                        href,
                    },
                    on: {
                        click: (event: MouseEvent) => {
                            event.preventDefault()
                            event.stopImmediatePropagation()
                            this.openImageEditDialog(item)
                        },
                    },
                })
            }
            // Other reference
            else {
                buttons.unshift({
                    tag: 'a',
                    iconClass: 'rz-icon-ri--edit-line',
                    emphasis: 'primary',
                    attributes: {
                        href,
                    },
                })
            }
        }

        const cardOptions: RzCardOptions = {
            tag: 'li',
            image: {
                src: item.thumbnail80,
            },
            buttonGroup: {
                buttons,
            },
        }

        if (this.acceptEntity !== 'document') {
            cardOptions.title = item.displayable
        }

        const element = rzCardRenderer(cardOptions)

        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = `${this.drawerName}[${index}][document]`
        input.value = item.id.toString()
        element.appendChild(input)

        return element
    }

    removeItem(item: RzDrawerItem) {
        const index = this.items.indexOf(item)

        if (index > -1) {
            this.items.splice(index, 1)

            // Remove DOM element
            const element = this.itemElements.get(item)

            if (element && element.parentNode) {
                element.parentNode.removeChild(element)
            }
            this.itemElements.delete(item)

            // Reindex remaining items
            this.reindexItems()
        }
    }

    reindexItems() {
        if (!this.listElement) {
            return
        }

        const inputs = this.listElement.querySelectorAll<HTMLInputElement>(
            'input[type="hidden"]',
        )

        inputs.forEach((input, index) => {
            input.name = `${this.drawerName}[${index}][document]`
        })
    }

    async initItems() {
        const items: ItemAttribute[] = JSON.parse(
            this.getAttribute('items') || '[]',
        )
        const entityType = this.acceptEntity

        if (
            !Array.isArray(items) ||
            items.length === 0 ||
            !entityType ||
            !this.listElement
        ) {
            return
        }

        // Format IDs
        const filteredIds = items.map((item) => {
            // If item is an object with a document property, extract it
            if (typeof item === 'object' && 'document' in item) {
                return item.document
            }

            return item
        })

        // Fetch items from API
        const response: { items?: RzDrawerItem[] } = await getItemsByIds(
            entityType,
            filteredIds,
        ).catch((error) => {
            console.error('Error fetching drawer items', error)
        })

        if (response && response.items) {
            // Use DocumentFragment for batch rendering to minimize reflows
            const fragment = document.createDocumentFragment()

            response.items.forEach((item, index) => {
                this.items.push(item)
                const element = this.createItemElement(item, index)
                this.itemElements.set(item, element)
                fragment.appendChild(element)
            })

            this.listElement.appendChild(fragment)
        }
    }

    // Image editing dialog
    openImageEditDialog(item: RzDrawerItem) {
        const dialog = document.createElement('document-edit-dialog')

        dialog.setAttribute(
            'template-path',
            this.getAttribute('document-alignment-template-path'),
        )
        dialog.setAttribute('title', item.classname)
        dialog.setAttribute(
            'edit-url',
            item.editItem + '?referer=' + window.location.pathname,
        )
        dialog.setAttribute('image-path', item.editImageUrl)
        dialog.setAttribute('image-width', String(item.editImageWidth))
        dialog.setAttribute('image-height', String(item.editImageHeight))
        dialog.setAttribute('input-base-name', this.drawerName)
        dialog.setAttribute('open', '')

        if (item.originalHotspot) {
            dialog.setAttribute(
                'original-hotspot',
                JSON.stringify(item.originalHotspot),
            )
        }

        document.body.appendChild(dialog)
    }
}
