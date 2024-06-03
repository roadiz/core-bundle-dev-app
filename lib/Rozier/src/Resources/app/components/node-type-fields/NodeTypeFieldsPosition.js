import $ from 'jquery'

/**
 * Node type fields position
 */
export default class NodeTypeFieldsPosition {
    /**
     * Constructor
     */
    constructor() {
        this.$list = $('.node-type-fields > .uk-sortable')
        this.currentRequest = null

        // Bind methods
        this.onSortableChange = this.onSortableChange.bind(this)

        this.init()
    }

    /**
     * Init
     */
    init() {
        if (this.$list.length && this.$list.children().length > 1) {
            this.$list.on('change.uk.sortable', this.onSortableChange)
        }
    }

    unbind() {
        if (this.$list.length && this.$list.children().length > 1) {
            this.$list.off('change.uk.sortable', this.onSortableChange)
        }
    }

    /**
     * @param event
     * @param list
     * @param element
     */
    async onSortableChange(event, list, element) {
        if (this.currentRequest && this.currentRequest.readyState !== 4) {
            this.currentRequest.abort()
        }

        let $element = $(element)
        let nodeTypeFieldId = parseInt($element.data('field-id'))
        let $sibling = $element.prev()
        let beforeFieldId = null
        let afterFieldId = null

        if ($sibling.length === 0) {
            $sibling = $element.next()
            beforeFieldId = parseInt($sibling.data('field-id'))
        } else {
            afterFieldId = parseInt($sibling.data('field-id'))
        }

        let postData = {
            _token: window.Rozier.ajaxToken,
            _action: 'updatePosition',
            nodeTypeFieldId: nodeTypeFieldId,
            beforeFieldId: beforeFieldId,
            afterFieldId: afterFieldId,
        }

        const response = await fetch(
            window.Rozier.routes.nodeTypesFieldAjaxEdit.replace('%nodeTypeFieldId%', nodeTypeFieldId),
            {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new URLSearchParams(postData),
            }
        )
        if (!response.ok) {
            const data = await response.json()
            window.UIkit.notify({
                message: data.title,
                status: 'danger',
                timeout: 3000,
                pos: 'top-center',
            })
        } else {
            const data = await response.json()
            window.UIkit.notify({
                message: data.responseText,
                status: data.status,
                timeout: 3000,
                pos: 'top-center',
            })
        }
    }
}
