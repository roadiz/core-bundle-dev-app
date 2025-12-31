import { getItemsByIds } from '~/api/DrawerApi'
import { RzButtonOptions } from '~/utils/component-renderer/rzButton'
import {
    RzCardOptions,
    rzCardRenderer,
} from '~/utils/component-renderer/rzCard'
import Sortable from 'sortablejs/modular/sortable.core.esm.js'

interface Hotspot {
    x: number
    y: number
}

interface Document {
    url?: string
    alt?: string | null
    embedId?: string | null
    embedPlatform?: string | null
    hotspot?: Hotspot | null
    imageAverageColor?: string
    imageHeight?: number
    imageWidth?: number
    mediaDuration?: number
    mimeType?: string
    processable?: boolean
    relativePath?: string
    type?: string
}

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
    hotspot?: Hotspot
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
    originalHotspot?: Hotspot | null
    previewHtml?: string
    processable?: boolean
    published?: boolean
    relativePath?: string
    shortMimeType?: string
    shortType?: string
    thumbnail?: Document | null
    thumbnail80?: string
}

interface DocumentItemAttribute {
    document?: number
    id?: number
    hotspot?: Hotspot | null
    imageCropAlignment?: string
}

type ItemAttribute = number | DocumentItemAttribute

const ITEM_CLASS_NAME = 'rz-drawer__item'

export class RzDrawer extends HTMLElement {
    fileUploadIsVisible: boolean = false
    fileUpload: HTMLElement | null = null
    items: RzDrawerItem[] = []
    listElement: HTMLElement | null = null
    itemElements: WeakMap<RzDrawerItem, HTMLElement> = new WeakMap()
    sortable: Sortable | null = null
    acceptEntity: string
    name: string
    sortableEnabled: boolean

    constructor() {
        super()

        // Initialize with attributes
        this.acceptEntity = this.getAttribute('accept-entity') || ''
        this.name = this.getAttribute('name') || ''
        this.sortableEnabled = this.hasAttribute('sortable')

        // Bindings
        this.onCommand = this.onCommand.bind(this)
        this.onAddDrawerItem = this.onAddDrawerItem.bind(this)
        this.onFileUploadSuccess = this.onFileUploadSuccess.bind(this)
    }

    connectedCallback() {
        this.initExplorer()
        this.initCommands()
        this.initItems()
        this.initSortable()
        this.initFileUpload()
    }

    disconnectedCallback() {
        this.destroyExplorer()
        this.destroyItems()
        this.destroyCommands()
        this.destroySortable()
        this.destroyFileUpload()
    }

    // COMMANDS
    initCommands() {
        // Listen for commands from external buttons
        this.addEventListener('command', this.onCommand)
    }

    destroyCommands() {
        this.removeEventListener('command', this.onCommand)
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

    // FILE UPLOAD
    initFileUpload() {
        this.fileUpload = this.querySelector('rz-file-upload')

        if (!this.fileUpload) {
            return
        }

        this.fileUpload.addEventListener('success', this.onFileUploadSuccess)
    }

    destroyFileUpload() {
        if (!this.fileUpload) {
            return
        }

        this.fileUpload.removeEventListener('success', this.onFileUploadSuccess)
    }

    onFileUploadSuccess(event: CustomEvent) {
        const item: RzDrawerItem = event.detail.response.document

        if (item) {
            this.appendItem(item)
        }
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

    // EXPLORER
    initExplorer() {
        // Listen for explorer add item events
        document.addEventListener('add-drawer-item', this.onAddDrawerItem)
    }

    destroyExplorer() {
        document.removeEventListener('add-drawer-item', this.onAddDrawerItem)
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
    async initItems() {
        const items: ItemAttribute[] = JSON.parse(
            this.getAttribute('items') || '[]',
        )
        const entityType = this.acceptEntity

        this.listElement = this.querySelector('[data-list]')

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

        // Get filters
        const providerClass = this.getAttribute('provider-class')
        const locale = this.getAttribute('locale')
        const providerOptions = JSON.parse(
            decodeURIComponent(this.getAttribute('provider-options')),
        )
        const nodeTypes = this.getAttribute('data-nodetypes')
        const nodeTypeField = this.getAttribute('data-nodetypefield')
        const nodeTypeName = this.getAttribute('data-nodetypename')

        // Merge filters into one object
        const filters = {
            nodeTypes,
            nodeTypeField,
            providerClass,
            providerOptions,
            nodeTypeName,
            _locale: locale,
        }

        // Fetch items from API
        const response: { items?: RzDrawerItem[] } = await getItemsByIds(
            entityType,
            filteredIds,
            filters,
        ).catch((error) => {
            console.error('Error fetching drawer items', error)
        })

        if (response && response.items) {
            // Use DocumentFragment for batch rendering to minimize reflows
            const fragment = document.createDocumentFragment()

            response.items.forEach((item, index) => {
                const newItem = { ...item }
                const itemData = items[index]

                if (itemData && typeof itemData === 'object') {
                    if ('hotspot' in itemData) {
                        newItem.hotspot = itemData.hotspot as Hotspot
                    }

                    if ('imageCropAlignment' in itemData) {
                        newItem.imageCropAlignment = itemData.imageCropAlignment
                    }
                }

                this.items.push(newItem)

                const element = this.createItemElement(newItem, index)
                this.itemElements.set(newItem, element)
                fragment.appendChild(element)
            })

            this.listElement.appendChild(fragment)
        }
    }

    destroyItems() {
        this.itemElements = new WeakMap()
    }

    createItemElement(item: RzDrawerItem, index: number): HTMLElement {
        const isDocument =
            item.isPdf || item.isImage || item.isVideo || item.isEmbed

        // Action buttons
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
            if (item.isImage && !item.isEmbed && !item.isVideo && !item.isPdf) {
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
            buttonGroup: {
                buttons,
            },
        }

        // Previewable image
        if (item.isImage) {
            cardOptions.buttonGroupTop = {
                gap: 'sm',
                size: 'sm',
                buttons: [
                    {
                        iconClass: 'rz-icon-ri--zoom-in-line',
                        emphasis: 'primary',
                        attributes: {
                            type: 'button', // do not submit form
                        },
                        on: {
                            click: () => {
                                document.dispatchEvent(
                                    new CustomEvent('show-preview', {
                                        detail: { document: item },
                                    }),
                                )
                            },
                        },
                    },
                ],
            }
        }

        let iconClass = ''

        // Private item
        if (item.isPrivate) {
            iconClass = 'rz-icon-ri--lock-2-line'
        } else {
            // Image thumbnail
            if ((item.isImage && item.thumbnail80) || item.thumbnail?.url) {
                cardOptions.image = {
                    src: item.thumbnail80 || item.thumbnail.url || '',
                }
            }

            // PDF icon
            if (item.isPdf) {
                iconClass = 'rz-icon-ri--file-pdf-2-line'
            } else if (item.isEmbed) {
                if (item.embedPlatform === 'vimeo') {
                    iconClass = 'rz-icon-ri--vimeo-fill'
                } else if (item.embedPlatform === 'youtube') {
                    iconClass = 'rz-icon-ri--youtube-fill'
                }
            } else if (item.isVideo) {
                iconClass = 'rz-icon-ri--file-video-fill'
            }
        }

        if (iconClass) {
            cardOptions.badge = {
                iconClass,
                size: 'md',
            }
        }

        // Title and overtitle (only if not a reference to a document)
        if (this.acceptEntity !== 'document') {
            cardOptions.overtitle = item.classname
            cardOptions.title = item.displayable
        }

        // Create card element
        const element = rzCardRenderer(cardOptions)

        element.classList.add(ITEM_CLASS_NAME)
        element.dataset.id = item.id ? item.id.toString() : ''
        element.dataset.inputBaseName = `${this.name}[${index}]`

        // Main hidden input for form submission
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = `${this.name}[${index}]${isDocument ? '[document]' : ''}`
        input.value = item.id.toString()
        element.appendChild(input)

        // Document hidden inputs for images
        if (item.isImage) {
            // Original hotspot
            const hotspotInput = document.createElement('input')
            hotspotInput.type = 'hidden'
            hotspotInput.name = `${this.name}[${index}][hotspot]`
            hotspotInput.value = item.hotspot
                ? JSON.stringify(item.hotspot)
                : 'null'
            element.appendChild(hotspotInput)

            // Image crop alignment
            const alignmentInput = document.createElement('input')
            alignmentInput.type = 'hidden'
            alignmentInput.name = `${this.name}[${index}][imageCropAlignment]`
            alignmentInput.value = item.imageCropAlignment || ''
            element.appendChild(alignmentInput)
        }

        return element
    }

    appendItem(item: RzDrawerItem) {
        if (!this.listElement) {
            return
        }

        this.items.push(item)
        const itemIndex = this.items.length - 1

        const element = this.createItemElement(item, itemIndex)
        this.itemElements.set(item, element)
        this.listElement.appendChild(element)

        this.dispatchLengthChange()
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

            this.dispatchLengthChange()
        }
    }

    reindexItems() {
        if (!this.listElement) {
            return
        }

        for (
            let i = 0, numChildren = this.listElement.children.length;
            i < numChildren;
            i++
        ) {
            const child = this.listElement.children[i] as HTMLElement
            // Get all hidden inputs related to this item
            const inputs = child.querySelectorAll<HTMLInputElement>(
                `input[type="hidden"][name^="${this.name}["]`,
            )

            // Update input base name following the new index
            child.dataset.inputBaseName = `${this.name}[${i}]`

            // Update each input name following the new index
            for (let j = 0; j < inputs.length; j++) {
                inputs[j].name = inputs[j].name.replace(/\[\d+\]/, `[${i}]`)
            }
        }
    }

    openImageEditDialog(item: RzDrawerItem) {
        const dialog = document.createElement('document-edit-dialog')

        if (this.listElement) {
            const itemElement: HTMLElement = this.listElement.querySelector(
                `[data-id="${item.id?.toString()}"]`,
            )

            if (itemElement) {
                const inputBaseName = itemElement.dataset.inputBaseName || ''

                if (inputBaseName) {
                    dialog.setAttribute('input-base-name', inputBaseName)
                }
            }
        }

        dialog.setAttribute(
            'template-path',
            this.getAttribute('document-alignment-template-path'),
        )
        dialog.setAttribute('title', item.classname)
        dialog.setAttribute(
            'edit-url',
            item.editItem + '?referer=' + window.location.pathname,
        )

        dialog.setAttribute('open', '')

        if (item.isImage) {
            dialog.setAttribute('image-path', item.editImageUrl)
            dialog.setAttribute('image-width', String(item.editImageWidth))
            dialog.setAttribute('image-height', String(item.editImageHeight))

            if (item.originalHotspot) {
                dialog.setAttribute(
                    'original-hotspot',
                    JSON.stringify(item.originalHotspot),
                )
            }
        }

        document.body.appendChild(dialog)
    }

    dispatchLengthChange() {
        this.dispatchEvent(
            new CustomEvent('length-change', {
                detail: { length: this.items.length },
                bubbles: true,
            }),
        )
    }

    // SORTABLE
    initSortable() {
        if (!this.listElement || this.sortable || !this.sortableEnabled) {
            return
        }

        this.sortable = Sortable.create(this.listElement, {
            animation: 150,
            onEnd: () => {
                // Reindex hidden inputs
                this.reindexItems()
            },
        })
    }

    destroySortable() {
        if (!this.sortable) return

        this.sortable.destroy()
        this.sortable = null
    }
}
