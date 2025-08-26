interface BoundListener {
    type: string
    target: Element | ArrayLike<Element> | null
    callback: EventListener
    options: AddEventListenerOptions | boolean
}

export default class RoadizElement extends HTMLElement {
    _boundListeners: Map<string, BoundListener>

    constructor() {
        super()

        this._boundListeners = new Map()
    }

    disconnectedCallback() {
        this.removeAllListeners()
    }

    _getBoundListenerKey(event: string, listener: EventListener): string {
        return `${event}_${listener.name}`
    }

    removeAllListeners() {
        this._boundListeners.forEach(({ target, type, callback, options }) => {
            this.off(target, type, callback, options)
        })

        this._boundListeners.clear()
    }

    on(
        target: Element | ArrayLike<Element> | string | null,
        type: string,
        callback: EventListener,
        options: AddEventListenerOptions | boolean = false,
        ...args: unknown[]
    ) {
        const targetElement = typeof target === 'string' ? document.querySelectorAll(target) : target

        if (!targetElement) {
            console.warn(`Target ${target} is not valid for ${type} listener`)
            return
        }

        const key = this._getBoundListenerKey(type, callback)

        if (this._boundListeners.has(key)) {
            console.warn(`Listener for type ${type} with callback ${callback.name} already exists`)
            return
        }

        const boundListener = callback.bind(this, ...args)

        this._boundListeners.set(key, { target: targetElement, type, callback: boundListener, options })

        if ('length' in targetElement) {
            for (let i = 0; i < targetElement.length; i++) {
                targetElement[i].addEventListener(type, boundListener, options)
            }
        } else {
            targetElement.addEventListener(type, boundListener, options)
        }
    }

    off(
        target: Element | ArrayLike<Element> | string | null,
        event: string,
        callback: EventListener,
        options: AddEventListenerOptions | boolean = false
    ) {
        const key = this._getBoundListenerKey(event, callback)
        const boundListener = this._boundListeners.get(key)

        if (!boundListener) return

        this._boundListeners.delete(key)

        const targetElement = typeof target === 'string' ? document.querySelectorAll(target) : target

        if (!targetElement) return

        if ('length' in targetElement) {
            for (let i = 0; i < targetElement.length; i++) {
                targetElement[i].removeEventListener(boundListener.type, boundListener.callback, options)
            }
        } else {
            targetElement.removeEventListener(boundListener.type, boundListener.callback, options)
        }
    }
}
