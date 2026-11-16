import { rzMessageRenderer } from '~/utils/component-renderer/rzMessage'
import { trans } from '~/utils/trans'

const ERROR_CLASS = 'rz-row-form-error'
// Keys added by FormErrorSerializer next to a message: exception class and raw (untranslated) message
const EXCEPTION_DETAIL_KEY = /_cause(_message)?$/

type FormErrorResponse = {
    errors?: unknown
}

/** Shown when the request fails without a validation message */
function saveFailedMessage() {
    return trans('form.row.save_failed')
}

function createError(message: string) {
    return rzMessageRenderer({
        text: message,
        color: 'error',
        attributes: { class: ERROR_CLASS, role: 'alert' },
    })
}

/** Translated messages of a serialized form error tree, minus the `*_cause` and `*_cause_message` exception details */
function flattenErrors(errors: unknown): string[] {
    if (typeof errors === 'string') return [errors]
    if (!errors || typeof errors !== 'object') return []

    return Object.entries(errors).flatMap(([key, value]) =>
        EXCEPTION_DETAIL_KEY.test(key) ? [] : flattenErrors(value),
    )
}

/** Error messages of the JSON answer: the `value` field errors, else every form error */
function getErrorMessages(data: FormErrorResponse): string[] {
    const { errors } = data
    const valueErrors =
        errors && typeof errors === 'object' && 'value' in errors
            ? flattenErrors(errors.value)
            : []

    return valueErrors.length ? valueErrors : flattenErrors(errors)
}

/** The validation errors of a failed answer, or a generic message when it carries none */
async function getFailureMessages(response: Response): Promise<string[]> {
    const data: FormErrorResponse | null = await response
        .json()
        .catch(() => null)
    const messages = data ? getErrorMessages(data) : []

    return messages.length ? messages : [saveFailedMessage()]
}

/**
 * Saves the form of a single table row (a setting, an attribute value) without leaving the page:
 * posts it to the current URL as JSON, shows the validation errors (or a generic one when the
 * request fails) under the form and refreshes the session toasts. A form with a file input is
 * submitted normally.
 */
export async function submitRowForm(form: HTMLFormElement) {
    if (form.querySelector('input[type=file]')) {
        form.requestSubmit()
        return
    }

    form.querySelectorAll(`.${ERROR_CLASS}`).forEach((error) => error.remove())

    window.dispatchEvent(new CustomEvent('requestLoaderShow'))

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        })

        if (!response.ok) {
            const messages = await getFailureMessages(response)
            form.append(...messages.map(createError))
        }
    } catch (error) {
        console.error(error)
        form.append(createError(saveFailedMessage()))
    } finally {
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    // Outside the try: a toast refresh failure must not report the row as unsaved
    await window.Rozier?.getMessages().catch(console.error)
}
