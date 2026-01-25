// HERE WE NEED JQUERY BECAUSE UI-KIT V2 REQUIRE JQUERY
export default class AttributeValuePosition {
    constructor() {
        this.$list = $('.attribute-value-forms > .uk-sortable')
        this.currentRequest = null
        // Bind methods
        this.onSortableChange = this.onSortableChange.bind(this)
        this.init()
    }

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
        if (event.target instanceof HTMLInputElement) {
            return
        }

        let $element = $(element)
        let attributeValueId = parseInt($element.data('id'))
        let $sibling = $element.prev()
        const payload = {
            csrfToken: window.RozierConfig.ajaxToken,
            id: attributeValueId,
        }

        if ($sibling.length === 0) {
            $sibling = $element.next()
            payload.nextId = parseInt($sibling.data('id'))
        } else {
            payload.prevId = parseInt($sibling.data('id'))
        }

        try {
            const response = await fetch(
                window.RozierConfig.routes.attributeValuePositionAjax,
                {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        // Required to prevent using this route as referer when login again
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new URLSearchParams(payload),
                },
            )
            if (!response.ok) {
                throw response
            }
            const data = await response.json()
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.responseText,
                        status: data.status,
                    },
                }),
            )
        } catch (response) {
            const data = await response.json()
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.title || '',
                        status: 'danger',
                    },
                }),
            )
        }
    }
}
