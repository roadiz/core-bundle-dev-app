/**
 * Extract a human-readable message from an AJAX error payload.
 *
 * Server error bodies are not uniform: Symfony's default JSON error renderer
 * uses `detail`, our custom handlers use `message`, and a few endpoints use
 * `error_message`. Accepts an already-parsed object or a raw JSON string.
 * Falls back to the translated forbidden message so a 40x never fails silently.
 *
 * A raw fetch/JS Error (network failure) is not a server body, so its technical
 * `.message` (e.g. "Failed to fetch") is ignored in favour of that fallback.
 *
 * @param data parsed JSON body, thrown payload, JSON string, or Error
 */
type ErrorPayload = {
    error_message?: string
    detail?: string
    message?: string
}

export function getResponseErrorMessage(input: unknown): string | undefined {
    let data: ErrorPayload | null = null
    if (typeof input === 'string') {
        try {
            data = JSON.parse(input)
        } catch {
            data = {}
        }
    } else if (input instanceof Error) {
        data = {}
    } else {
        data = input as ErrorPayload | null
    }
    return (
        data?.error_message ||
        data?.detail ||
        data?.message ||
        window.RozierConfig?.messages?.forbiddenPage
    )
}

export function dataURItoBlob(dataURI: string): Blob {
    const binary = atob(dataURI.split(',')[1])
    const array: number[] = []

    for (let i = 0; i < binary.length; i++) {
        array.push(binary.charCodeAt(i))
    }

    // separate out the mime component
    const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]

    return new Blob([new Uint8Array(array)], { type: mimeString })
}
