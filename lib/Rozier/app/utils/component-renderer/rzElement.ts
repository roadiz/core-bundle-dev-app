export type RzElement = {
    tag?: string
    is?: string
    attributes?: Record<string, string>
    innerText?: string
    innerHTML?: string
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

    return element
}
