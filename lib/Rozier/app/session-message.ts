import { get } from '~/utils/http'

type SessionMessagesResponse = {
    messages?: {
        confirm?: string[]
        error?: string[]
    }
}

export async function fetchSessionMessages() {
    try {
        const data = await get<SessionMessagesResponse>('ajaxSessionMessages', {
            // AjaxSessionMessages validates this token on GET
            csrfField: '_csrf_token',
        })
        return data?.messages
    } catch (error) {
        // Session messages are best effort: an expired session is reported by LoginCheckService
        console.warn('Session messages could not be fetched', error)
        return null
    }
}

export async function dispatchSessionToast() {
    const messages = await fetchSessionMessages()
    if (!messages) return

    if (messages.confirm && messages.confirm.length > 0) {
        messages.confirm.forEach((message) => {
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: message,
                        status: 'success',
                    },
                }),
            )
        })
    }
    if (messages.error && messages.error.length > 0) {
        messages.error.forEach((message) => {
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: message,
                        status: 'danger',
                    },
                }),
            )
        })
    }
}
