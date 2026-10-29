type TransParams = Record<string, string | number>

const PLACEHOLDER_PATTERN = /%([^%]+)%/g

export function trans(key: string, params: TransParams = {}) {
    const messages = window.RozierConfig?.messages as
        | Record<string, unknown>
        | undefined
    const template = messages?.[key]
    const message = typeof template === 'string' ? template : key

    return message.replace(PLACEHOLDER_PATTERN, (match, name) => {
        const value = params[name]

        return value === undefined || value === null ? match : String(value)
    })
}
