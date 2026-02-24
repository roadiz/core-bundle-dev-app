import FormFieldLengthIndicator from '~/utils/FormFieldLengthIndicator'
import { trans } from '~/utils/trans'
import type RzTablist from './RzTablist'

export default class RzFormField extends HTMLElement {
    lengthIndicator: FormFieldLengthIndicator | null = null

    constructor() {
        super()

        this.lengthIndicator = new FormFieldLengthIndicator()
    }

    onInvalid(error: Event) {
        const input = error.target as
            | HTMLInputElement
            | HTMLTextAreaElement
            | HTMLSelectElement

        if (!input) return

        if (!input.checkVisibility()) {
            const tabList = document.querySelector(
                'rz-tablist',
            ) as RzTablist | null
            tabList?.setTabVisibilityFromElement(input)
        }

        const inputLabelElement =
            document.querySelector<HTMLElement>(`[for="${input.id}"]`) ||
            input.closest<HTMLElement>('.rz-form-field__head__label')
        const inputLabelText = inputLabelElement?.innerText || 'unknown'
        const message = trans('form.field.invalid', { label: inputLabelText })

        window.dispatchEvent(
            new CustomEvent('pushToast', {
                detail: {
                    message: message,
                    status: 'danger',
                },
            }),
        )
    }

    connectedCallback() {
        this.lengthIndicator?.init(this)

        this.querySelectorAll('input, textarea, select').forEach((element) => {
            element.addEventListener('invalid', this.onInvalid)
        })
    }

    disconnectedCallback() {
        this.lengthIndicator?.dispose()
        this.lengthIndicator = null

        this.querySelectorAll('input, textarea, select').forEach((element) => {
            element.removeEventListener('invalid', this.onInvalid)
        })
    }
}
