import FormFieldLengthIndicator from '~/utils/FormFieldLengthIndicator'

export default class RzFormField extends HTMLElement {
    lengthIndicator: FormFieldLengthIndicator | null = null

    constructor() {
        super()

        this.lengthIndicator = new FormFieldLengthIndicator()
    }

    connectedCallback() {
        this.lengthIndicator?.init(this)
    }

    disconnectedCallback() {
        this.lengthIndicator?.dispose()
        this.lengthIndicator = null
    }
}
