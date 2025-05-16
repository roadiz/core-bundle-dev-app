import {slideDown, slideUp} from "../../utils/animation";

export default class DocumentsBulk {
    /**
     * Create a documents bulk
     */
    constructor() {
        this.documentsCheckboxes = document.querySelectorAll('input.document-checkbox')
        this.documentsIdBulkFolders = document.querySelectorAll('input.document-id-bulk-folder')
        this.actionsMenu = document.querySelector('.documents-bulk-actions')
        this.documentsFolderButton = document.querySelector('.uk-button-bulk-folder-documents')
        this.documentsFolderCont = document.querySelector('.documents-bulk-folder-cont')
        this.documentsSelectAll = document.querySelector('.uk-button-select-all')
        this.documentsDeselectAll = document.querySelector('.uk-button-bulk-deselect')
        if (this.actionsMenu === null) {
            return
        }
        this.bulkDeleteButton = this.actionsMenu.querySelector('.uk-button-bulk-delete-documents')
        this.bulkDownloadButton = this.actionsMenu.querySelector('.uk-button-bulk-download-documents')
        this.documentsFolderOpen = false
        this.documentsIds = null

        this.onCheckboxChange = this.onCheckboxChange.bind(this)
        this.onBulkDelete = this.onBulkDelete.bind(this)
        this.documentsFolderButtonClick = this.documentsFolderButtonClick.bind(this)
        this.onBulkDownload = this.onBulkDownload.bind(this)
        this.onSelectAll = this.onSelectAll.bind(this)
        this.onDeselectAll = this.onDeselectAll.bind(this)

        if (this.documentsCheckboxes.length) {
            this.init()
        }
    }

    init() {
        this.documentsCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', this.onCheckboxChange)
        })
        this.bulkDeleteButton.addEventListener('click', this.onBulkDelete)
        this.documentsFolderButton.addEventListener('click', this.documentsFolderButtonClick)
        this.bulkDownloadButton.addEventListener('click', this.onBulkDownload)
        this.documentsSelectAll.addEventListener('click', this.onSelectAll)
        this.documentsDeselectAll.addEventListener('click', this.onDeselectAll)
    }

    unbind() {
        if (this.documentsCheckboxes.length) {
            this.documentsCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', this.onCheckboxChange)
            })
            this.bulkDeleteButton.removeEventListener('click', this.onBulkDelete)
            this.documentsFolderButton.removeEventListener('click', this.documentsFolderButtonClick)
            this.bulkDownloadButton.removeEventListener('click', this.onBulkDownload)
            this.documentsSelectAll.removeEventListener('click', this.onSelectAll)
            this.documentsDeselectAll.removeEventListener('click', this.onDeselectAll)
        }
    }

    onSelectAll(event) {
        event.preventDefault()
        this.documentsCheckboxes.forEach((checkbox) => {
            checkbox.checked = true;
        })
        this.onCheckboxChange(null)
        return false
    }

    onDeselectAll(event) {
        event.preventDefault()
        this.documentsCheckboxes.forEach((checkbox) => {
            checkbox.checked = false;
        })
        this.onCheckboxChange(null)
        return false
    }

    /**
     * On checkbox change
     */
    onCheckboxChange(event) {
        if (event !== null) {
            event.preventDefault()
        }
        this.documentsIds = []
        const documentCheckboxChecked = document.querySelectorAll('input.document-checkbox:checked')

        documentCheckboxChecked.forEach((domElement) => {
            this.documentsIds.push(domElement.value)
        })

        if (this.documentsIdBulkFolders.length) {
            this.documentsIdBulkFolders.forEach((idBulkFolder) => {
                idBulkFolder.value = this.documentsIds.join(',')
            })
        }

        if (this.documentsIds.length > 0) {
            this.showActions()
        } else {
            this.hideActions()
        }
    }

    /**
     * On bulk delete
     * @returns {boolean}
     */
    onBulkDelete(event) {
        event.preventDefault()
        if (this.documentsIds.length > 0) {
            history.pushState(
                {
                    headerData: {
                        documents: this.documentsIds,
                    },
                },
                null,
                window.Rozier.routes.documentsBulkDeletePage
            )

            window.Rozier.lazyload.onPopState(null)
        }

        return false
    }

    /**
     * On bulk Download
     * @returns {boolean}
     */
    onBulkDownload(event) {
        event.preventDefault()
        if (this.documentsIds.length > 0) {
            history.pushState(
                {
                    headerData: {
                        documents: this.documentsIds,
                    },
                },
                null,
                window.Rozier.routes.documentsBulkDownloadPage
            )

            window.Rozier.lazyload.onPopState(null)
        }

        return false
    }

    /**
     * Show actions
     */
    async showActions() {
        await slideDown(this.actionsMenu)
    }

    /**
     * Hide actions
     */
    async hideActions() {
        await slideUp(this.actionsMenu)
    }

    /**
     * Documents folder button click
     * @returns {boolean}
     */
    async documentsFolderButtonClick(event) {
        event.preventDefault()
        if (!this.documentsFolderOpen) {
            await slideDown(this.documentsFolderCont)
            this.documentsFolderOpen = true
        } else {
            await slideUp(this.documentsFolderCont)
        }

        return false
    }
}
