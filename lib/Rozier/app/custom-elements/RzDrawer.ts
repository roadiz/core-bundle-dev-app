import { getItemsByIds } from '~/api/DrawerApi'
import Sortable from 'sortablejs/modular/sortable.core.esm.js'
import { createDrawerItemElement } from '~/utils/drawer/create-drawer-item-element'
import { Hotspot, ItemAttribute, RzDrawerItem } from '~/utils/drawer/types'

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
    validationProxy: HTMLSelectElement | null = null

    constructor() {
        super()

        // Bindings
        this.onCommand = this.onCommand.bind(this)
        this.onAddDrawerItem = this.onAddDrawerItem.bind(this)
        this.onFileUploadSuccess = this.onFileUploadSuccess.bind(this)
    }

    connectedCallback() {
        // Initialize with attributes
        this.acceptEntity = this.getAttribute('accept-entity') || ''
        this.name = this.getAttribute('name') || ''
        this.sortableEnabled = this.hasAttribute('sortable')

        this.initExplorer()
        this.initCommands()
        this.initItems()
        this.initSortable()
        this.initFileUpload()
        this.initValidationProxy()
    }

    disconnectedCallback() {
        this.destroyExplorer()
        this.destroyItems()
        this.destroyCommands()
        this.destroySortable()
        this.destroyFileUpload()
        this.destroyValidationProxy()
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

    // VALIDATION PROXY
    initValidationProxy() {
        this.validationProxy = this.querySelector<HTMLSelectElement>(
            '[data-drawer-validation-proxy]',
        )
    }

    destroyValidationProxy() {
        this.validationProxy = null
    }

    syncValidationProxyOptions(ids: Array<string | number>) {
        if (!this.validationProxy) {
            return
        }

        this.validationProxy.innerHTML = ''

        ids.forEach((id) => {
            const option = document.createElement('option')
            option.value = id.toString()
            option.selected = true
            option.setAttribute('selected', 'true')
            this.validationProxy?.appendChild(option)
        })
    }

    updateValidationProxy() {
        if (!this.validationProxy) {
            return
        }

        const ids = this.items
            .map((item) => item?.id)
            .filter(
                (value): value is number =>
                    typeof value === 'number' || typeof value === 'string',
            )

        this.syncValidationProxyOptions(ids)
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

    getFiltersFromAttributes() {
        const providerClass = this.getAttribute('provider-class')
        const locale = this.getAttribute('locale')
        const providerOptionsAttribute = this.getAttribute('provider-options')
        const providerOptions = providerOptionsAttribute
            ? JSON.parse(decodeURIComponent(providerOptionsAttribute))
            : null
        const nodeTypes = this.getAttribute('data-nodetypes')
        const nodeTypeField = this.getAttribute('data-nodetypefield')
        const nodeTypeName = this.getAttribute('data-nodetypename')

        return {
            nodeTypes,
            nodeTypeField,
            providerClass,
            providerOptions,
            nodeTypeName,
            _locale: locale,
        }
    }

    openExplorer() {
        document.dispatchEvent(
            new CustomEvent('show-explorer', {
                detail: {
                    id: this.getAttribute('id'),
                    acceptEntity: this.getAttribute('accept-entity'),
                    filters: this.getFiltersFromAttributes(),
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
            if (item && typeof item === 'object' && 'document' in item) {
                return item.document
            }

            return item
        })

        const filters = this.getFiltersFromAttributes()

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

        this.updateValidationProxy()
    }

    destroyItems() {
        this.itemElements = new WeakMap()
    }

    createItemElement(item: RzDrawerItem, index: number): HTMLElement {
        const element = createDrawerItemElement({
            item,
            index,
            acceptEntity: this.acceptEntity,
            name: this.name,
            onRemoveClick: (itemToRemove: RzDrawerItem) => {
                this.removeItem(itemToRemove)
            },
            onEditClick: (itemToEdit: RzDrawerItem) => {
                this.openImageEditDialog(itemToEdit)
            },
        })

        element.classList.add(ITEM_CLASS_NAME)

        return element
    }

    appendItem(item: RzDrawerItem) {
        if (!this.listElement || this.itemElements.has(item)) {
            return
        }

        this.items.push(item)
        const itemIndex = this.items.length - 1

        const element = this.createItemElement(item, itemIndex)
        this.itemElements.set(item, element)
        this.listElement.appendChild(element)

        this.updateValidationProxy()
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

            this.updateValidationProxy()
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
