import {slideDown, slideUp} from "../../utils/animation";

/**
 * Tags bulk
 */
export default class TagsBulk {
    /**
     * Create Tags bulk
     */
    constructor() {
        this.tagsCheckboxes = document.querySelectorAll('input.tag-checkbox')
        this.tagsIdBulkTags = document.querySelectorAll('input.tags-id-bulk-tags')
        this.tagsIdBulkStatus = document.querySelectorAll('input.tags-id-bulk-status')
        this.actionsMenu = document.querySelector('.tags-bulk-actions')

        this.tagsFolderButton = document.querySelector('.uk-button-bulk-folder-tags')
        this.tagsFolderCont = document.querySelector('.tags-bulk-folder-cont')

        this.tagsStatusButton = document.querySelector('.uk-button-bulk-status-tags')
        this.tagsStatusCont = document.querySelector('.tags-bulk-status-cont')

        this.tagsSelectAll = document.querySelector('.uk-button-select-all')
        this.tagsDeselectAll = document.querySelector('.uk-button-bulk-deselect')

        this.tagsFolderOpen = false
        this.tagsStatusOpen = false
        this.tagsIds = null

        this.onCheckboxChange = this.onCheckboxChange.bind(this)
        this.tagsFolderButtonClick = this.tagsFolderButtonClick.bind(this)
        this.tagsStatusButtonClick = this.tagsStatusButtonClick.bind(this)
        this.onSelectAll = this.onSelectAll.bind(this)
        this.onDeselectAll = this.onDeselectAll.bind(this)

        if (this.tagsCheckboxes.length) {
            this.init()
        }
    }

    /**
     * Init
     */
    init() {
        this.tagsCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', this.onCheckboxChange)
        })
        if (this.tagsStatusButton !== null) {
            this.tagsStatusButton.addEventListener('click', this.tagsStatusButtonClick)
        }
        if (this.tagsFolderButton !== null) {
            this.tagsFolderButton.addEventListener('click', this.tagsFolderButtonClick)
        }
        if (this.tagsSelectAll !== null) {
            this.tagsSelectAll.addEventListener('click', this.onSelectAll)
        }
        if (this.tagsDeselectAll !== null) {
            this.tagsDeselectAll.addEventListener('click', this.onDeselectAll)
        }
    }

    unbind() {
        if (this.tagsCheckboxes.length) {
            this.tagsCheckboxes.forEach((checkbox) => {
                checkbox.removeEventListener('change', this.onCheckboxChange)
            })
            if (this.tagsStatusButton !== null) {
                this.tagsStatusButton.removeEventListener('click', this.tagsStatusButtonClick)
            }
            if (this.tagsFolderButton !== null) {
                this.tagsFolderButton.removeEventListener('click', this.tagsFolderButtonClick)
            }
            if (this.tagsSelectAll !== null) {
                this.tagsSelectAll.removeEventListener('click', this.onSelectAll)
            }
            if (this.tagsDeselectAll !== null) {
                this.tagsDeselectAll.removeEventListener('click', this.onDeselectAll)
            }
        }
    }

    onSelectAll() {
        this.tagsCheckboxes.forEach((checkbox) => {
            checkbox.checked = true
        })
        this.onCheckboxChange(null)

        return false
    }

    onDeselectAll() {
        this.tagsCheckboxes.forEach((checkbox) => {
            checkbox.checked = false
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
        this.tagsIds = []
        const tagCheckboxChecked = document.querySelectorAll('input.tag-checkbox:checked')

        tagCheckboxChecked.forEach((domElement) => {
            this.tagsIds.push(domElement.value)
        })

        if (this.tagsIdBulkTags.length) {
            this.tagsIdBulkTags.forEach((idBulkTag) => {
                idBulkTag.value = this.tagsIds.join(',')
            })
        }
        if (this.tagsIdBulkStatus.length) {
            this.tagsIdBulkStatus.forEach((idBulkStatus) => {
                idBulkStatus.value = this.tagsIds.join(',')
            })
        }

        if (this.tagsIds.length > 0) {
            this.showActions()
        } else {
            this.hideActions()
        }

        return false
    }

    /**
     * On bulk delete
     */
    onBulkDelete(event) {
        event.preventDefault()
        if (this.tagsIds.length > 0) {
            history.pushState(
                {
                    headerData: {
                        tags: this.tagsIds,
                    },
                },
                null,
                window.Rozier.routes.tagsBulkDeletePage
            )

            window.Rozier.lazyload.onPopState(null)
        }

        return false
    }

    /**
     * Show actions
     * @return {[type]} [description]
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
     * Tags folder button click
     */
    async tagsFolderButtonClick() {
        if (!this.tagsFolderOpen) {
            await slideUp(this.tagsStatusCont)
            this.tagsStatusOpen = false

            await slideDown(this.tagsFolderCont)
            this.tagsFolderOpen = true
        } else {
            await slideUp(this.tagsFolderCont)
            this.tagsFolderOpen = false
        }

        return false
    }

    /**
     * Tags status button click
     */
    async tagsStatusButtonClick() {
        if (!this.tagsStatusOpen) {
            await slideUp(this.tagsFolderCont)
            this.tagsFolderOpen = false

            await slideDown(this.tagsStatusCont)
            this.tagsStatusOpen = true
        } else {
            await slideUp(this.tagsStatusCont)
            this.tagsStatusOpen = false
        }

        return false
    }
}
