import { errorMessage, HttpError, isAbortError } from '~/utils/http'

export function pushToast(message: string, status: 'success' | 'danger') {
    window.dispatchEvent(
        new CustomEvent('pushToast', { detail: { message, status } }),
    )
}

/** One toast for a failed call; an aborted call shows nothing */
export function pushErrorToast(error: unknown) {
    if (isAbortError(error)) return
    console.error(error)
    pushToast(
        error instanceof HttpError ? error.message : errorMessage(null),
        'danger',
    )
}
