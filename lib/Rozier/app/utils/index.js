/**
 * Extract a human-readable message from an AJAX error payload.
 *
 * Server error bodies are not uniform: Symfony's default JSON error renderer
 * uses `detail`, our custom handlers use `message`, and a few endpoints use
 * `error_message`. Accepts an already-parsed object or a raw JSON string.
 * Falls back to the translated forbidden message so a 40x never fails silently.
 *
 * @param {object|string} data parsed JSON body, thrown payload, or JSON string
 * @returns {string}
 */
export function getResponseErrorMessage(data) {
    if (typeof data === 'string') {
        try {
            data = JSON.parse(data)
        } catch {
            data = {}
        }
    }
    return (
        data?.error_message ||
        data?.detail ||
        data?.message ||
        window.RozierConfig?.messages?.forbiddenPage
    )
}

export function dataURItoBlob(dataURI) {
    let binary = atob(dataURI.split(',')[1])
    let array = []

    for (let i = 0; i < binary.length; i++) {
        array.push(binary.charCodeAt(i))
    }

    // separate out the mime component
    const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]

    return new Blob([new Uint8Array(array)], { type: mimeString })
}
