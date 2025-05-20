import $ from 'jquery'

// HERE WE NEED JQUERY BECAUSE WE USE TAGEDITOR
export default class TagAutocomplete {
    constructor() {
        this.$input = $('.rz-tag-autocomplete').eq(0)
        this.initialUrl = this.$input.attr('data-get-url')
        this.placeholder = this.$input.attr('placeholder')
        this.initialTags = []

        this.init()
    }

    init() {
        if (typeof this.initialUrl !== 'undefined' && this.initialUrl !== '') {
            $.getJSON(
                this.initialUrl,
                {
                    _action: 'getNodeTags',
                    _token: window.RozierConfig.ajaxToken,
                },
                (data) => {
                    this.initialTags = data
                    this.initAutocomplete()
                }
            )
        } else {
            this.initAutocomplete()
        }
    }

    unbind() {}

    split(val) {
        return val.split(/,\s*/)
    }

    extractLast(term) {
        return this.split(term).pop()
    }

    initAutocomplete() {
        this.$input.tagEditor({
            autocomplete: {
                delay: 0.3, // show suggestions immediately
                position: {
                    collision: 'flip', // automatic menu position up/down
                },
                source: (request, response) => {
                    $.getJSON(
                        window.RozierConfig.routes.tagAjaxSearch,
                        {
                            _action: 'tagAutocomplete',
                            _token: window.RozierConfig.ajaxToken,
                            search: this.extractLast(request.term),
                        },
                        response
                    )
                },
            },
            placeholder: this.placeholder,
            initialTags: this.initialTags,
            animateDelete: 0,
        })
    }
}
