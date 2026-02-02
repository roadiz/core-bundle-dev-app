export type RzElement = {
    tag?: string
    is?: string
    attributes?: Record<string, string>
    innerText?: string
    innerHTML?: string
    on?: Record<string, EventListener>
}

export function rzElement(options: RzElement) {
    const { tag, is, attributes } = options

    const element = document.createElement(
        tag || 'div',
        is ? { is } : undefined,
    )

    if (options.is) {
        element.setAttribute('is', options.is)
    }

    Object.entries(attributes || {}).forEach(([key, value]) => {
        if (typeof value === 'undefined') return
        element.setAttribute(key, String(value))
    })

    if (options.innerHTML) {
        element.innerHTML = options.innerHTML
    } else if (options.innerText) {
        element.innerText = options.innerText
    }

    if (options.on) {
        Object.entries(options.on).forEach(([eventName, listener]) => {
            if (typeof listener !== 'function') {
                console.warn(
                    `Listener for event "${eventName}" is not a function.`,
                )
                return
            }

            if (typeof eventName !== 'string') {
                console.warn(`Event name must be a string.`)
                return
            }

            element.addEventListener(eventName, listener)
        })
    }

    return element
}
