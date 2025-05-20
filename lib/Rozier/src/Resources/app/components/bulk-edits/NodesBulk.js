import {slideDown, slideUp} from "../../utils/animation";

/**
 * Nodes bulk
 */
export default class NodesBulk {
    constructor() {
        this.$nodesCheckboxes = document.querySelectorAll('input.node-checkbox')
        this.$nodesIdBulkTags = document.querySelectorAll('input.nodes-id-bulk-tags')
        this.$nodesIdBulkStatus = document.querySelectorAll('input.nodes-id-bulk-status')
        this.$actionsMenu = document.querySelector('.nodes-bulk-actions')

        this.$nodesFolderButton = document.querySelector('.uk-button-bulk-folder-nodes')
        this.$nodesFolderCont = document.querySelector('.nodes-bulk-folder-cont')

        this.$nodesStatusButton = document.querySelector('.uk-button-bulk-status-nodes')
        this.$nodesStatusCont = document.querySelector('.nodes-bulk-status-cont')

        this.$nodesSelectAll = document.querySelector('.uk-button-select-all')
        this.$nodesDeselectAll = document.querySelector('.uk-button-bulk-deselect')

        this.nodesFolderOpen = false
        this.nodesStatusOpen = false
        this.nodesIds = null

        this.onCheckboxChange = this.onCheckboxChange.bind(this)
        this.nodesFolderButtonClick = this.nodesFolderButtonClick.bind(this)
        this.nodesStatusButtonClick = this.nodesStatusButtonClick.bind(this)
        this.onSelectAll = this.onSelectAll.bind(this)
        this.onDeselectAll = this.onDeselectAll.bind(this)

        if (this.$nodesCheckboxes.length) {
            this.init()
        }
    }

    /**
     * Init
     */
    init() {
        this.$nodesCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', this.onCheckboxChange)
        })
        this.$nodesFolderButton.addEventListener('click', this.nodesFolderButtonClick)
        this.$nodesStatusButton.addEventListener('click', this.nodesStatusButtonClick)
        this.$nodesSelectAll.addEventListener('click', this.onSelectAll)
        this.$nodesDeselectAll.addEventListener('click', this.onDeselectAll)
    }

    unbind() {
        if (this.$nodesCheckboxes.length) {
            this.$nodesCheckboxes.forEach((checkbox) => {
                checkbox.removeEventListener('change', this.onCheckboxChange)
            })
            this.$nodesFolderButton.removeEventListener('click', this.nodesFolderButtonClick)
            this.$nodesStatusButton.removeEventListener('click', this.nodesStatusButtonClick)
            this.$nodesSelectAll.removeEventListener('click', this.onSelectAll)
            this.$nodesDeselectAll.removeEventListener('click', this.onDeselectAll)
        }
    }

    onSelectAll() {
        this.$nodesCheckboxes.forEach((checkbox) => {
            checkbox.checked = true;
        })
        this.onCheckboxChange(null)
        return false
    }

    onDeselectAll() {
        this.$nodesCheckboxes.forEach((checkbox) => {
            checkbox.checked = false;
        })
        this.onCheckboxChange(null)
        return false
    }

    /**
     * On checkbox change
     */
    onCheckboxChange() {
        this.nodesIds = []
        const nodeCheckboxChecked = document.querySelectorAll('input.node-checkbox:checked')

        nodeCheckboxChecked.forEach((domElement) => {
            this.nodesIds.push(domElement.value)
        })

        if (this.$nodesIdBulkTags.length) {
            this.$nodesIdBulkTags.forEach((idBulkTag) => {
                idBulkTag.value = this.nodesIds.join(',')
            })
        }

        if (this.$nodesIdBulkStatus.length) {
            this.$nodesIdBulkStatus.forEach((idBulkStatus) => {
                idBulkStatus.value = this.nodesIds.join(',')
            })
        }

        if (this.nodesIds.length > 0) {
            this.showActions()
        } else {
            this.hideActions()
        }

        return false
    }

    /**
     * On bulk delete
     */
    onBulkDelete() {
        if (this.nodesIds.length > 0) {
            history.pushState(
                {
                    headerData: {
                        nodes: this.nodesIds,
                    },
                },
                null,
                window.RozierConfig.routes.nodesBulkDeletePage
            )

            window.Rozier.lazyload.onPopState(null)
        }

        return false
    }

    /**
     * Show actions
     */
    async showActions() {
        await slideDown(this.$actionsMenu)
    }

    /**
     * Hide actions
     */
    async hideActions() {
        await slideUp(this.$actionsMenu)
    }

    /**
     * Nodes folder button click
     */
    async nodesFolderButtonClick() {
        if (!this.nodesFolderOpen) {
            await slideUp(this.$nodesStatusCont)
            this.nodesStatusOpen = false

            await slideDown(this.$nodesFolderCont)
            this.nodesFolderOpen = true
        } else {
            await slideUp(this.$nodesFolderCont)
            this.nodesFolderOpen = false
        }

        return false
    }

    /**
     * Nodes status button click
     */
    async nodesStatusButtonClick() {
        if (!this.nodesStatusOpen) {
            await slideUp(this.$nodesFolderCont)
            this.nodesFolderOpen = false

            await slideDown(this.$nodesStatusCont)
            this.nodesStatusOpen = true
        } else {
            await slideUp(this.$nodesStatusCont)
            this.nodesStatusOpen = false
        }

        return false
    }
}
