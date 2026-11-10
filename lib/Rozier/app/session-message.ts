type SessionMessagesResponse = {
    messages?: {
        confirm?: string[]
        error?: string[]
    }
}

export async function fetchSessionMessages() {
    const query = new URLSearchParams({
        _csrf_token: window.RozierConfig.ajaxToken,
    })

    const url =
        window.RozierConfig.routes.ajaxSessionMessages + '?' + query.toString()

    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    })

    if (!response.ok) {
        return null
    }

    const data = (await response.json()) as SessionMessagesResponse
    return data?.messages
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
