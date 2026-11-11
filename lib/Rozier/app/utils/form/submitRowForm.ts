import { rzMessageRenderer } from '~/utils/component-renderer/rzMessage'
import { trans } from '~/utils/trans'

const ERROR_CLASS = 'rz-row-form-error'

type FormErrors = {
    errors?: unknown
}

function createError(message: string) {
    return rzMessageRenderer({
        text: message,
        color: 'error',
        attributes: { class: ERROR_CLASS, role: 'alert' },
    })
}

/** String leaves of a serialized form error tree, minus the `*_cause` exception class names */
function flattenErrors(errors: unknown): string[] {
    if (typeof errors === 'string') return [errors]
    if (!errors || typeof errors !== 'object') return []

    return Object.entries(errors).flatMap(([key, value]) =>
        key.endsWith('_cause') ? [] : flattenErrors(value),
    )
}

/** Error messages of the JSON answer: the `value` field errors, else every form error */
function getErrorMessages(data: FormErrors): string[] {
    const { errors } = data
    const valueErrors =
        errors && typeof errors === 'object' && 'value' in errors
            ? flattenErrors(errors.value)
            : []

    return valueErrors.length ? valueErrors : flattenErrors(errors)
}

/** The validation errors of a failed answer, or a generic message when it carries none */
async function getFailureMessages(response: Response): Promise<string[]> {
    const data: FormErrors | null = await response.json().catch(() => null)
    const messages = data ? getErrorMessages(data) : []

    return messages.length ? messages : [trans('form.row.save_failed')]
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
        form.append(createError(trans('form.row.save_failed')))
    } finally {
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }

    // Outside the try: a toast refresh failure must not report the row as unsaved
    await window.Rozier?.getMessages().catch(console.error)
}
