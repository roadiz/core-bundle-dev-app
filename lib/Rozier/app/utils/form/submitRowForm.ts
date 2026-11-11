import { rzMessageRenderer } from '~/utils/component-renderer/rzMessage'

const ERROR_CLASS = 'rz-row-form-error'

type FormErrors = {
    errors?: Record<string, unknown> & { value?: Record<string, string> }
}

function createError(message: string) {
    return rzMessageRenderer({
        text: message,
        color: 'error',
        attributes: { class: ERROR_CLASS, role: 'alert' },
    })
}

/** Error messages of the JSON answer: the `value` field errors, else the first form error */
function getErrorMessages(data: FormErrors): string[] {
    if (!data.errors) return []

    if (data.errors.value) return Object.values(data.errors.value).map(String)

    const [first] = Object.values(data.errors)
    return first === undefined ? [] : [String(first)]
}

/**
 * Saves the form of a single table row (a setting, an attribute value) without leaving the page:
 * posts it to the current URL as JSON, shows the validation errors under the form and refreshes
 * the session toasts. A form with a file input is submitted normally.
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
            const data: FormErrors = await response.json()
            form.append(...getErrorMessages(data).map(createError))
        }

        await window.Rozier?.getMessages()
    } catch (error) {
        console.error(error)
    } finally {
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }
}
