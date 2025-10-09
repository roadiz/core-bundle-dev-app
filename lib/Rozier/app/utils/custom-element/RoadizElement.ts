interface Listener {
    callback: EventListener
    options: AddEventListenerOptions | boolean
    boundCallback: EventListener
}

export default class RoadizElement extends HTMLElement {
    _listeners: Map<Element, Map<string, Listener[]>>

    constructor() {
        super()

        this._listeners = new Map()
    }

    disconnectedCallback() {
        // Clean up all event listeners
        this.unlistenAll()
    }

    /**
     * Add event listener(s) to target element(s) with automatic cleanup
     * @param target - Element or collection of elements to listen to
     * @param eventType - Event type to listen for
     * @param callback - Event handler function
     * @param options - Event listener options
     * @param args - Additional arguments to bind to the callback
     */
    listen(
        target: Element | ArrayLike<Element>,
        eventType: string,
        callback: EventListener,
        options: AddEventListenerOptions | boolean = false,
        ...args: unknown[]
    ) {
        if (!target) {
            console.warn(
                `Target ${target} is not valid for ${eventType} listener`,
            )
            return
        }

        if (!eventType) {
            console.warn(
                `Event type ${eventType} is not valid for listener on target ${target}`,
            )
            return
        }

        if (typeof callback !== 'function') {
            console.warn(
                `Callback for ${eventType} listener on target ${target} is not a function`,
            )
            return
        }

        // Normalize target to an array
        const targetList = 'length' in target ? Array.from(target) : [target]

        for (let i = 0; i < targetList.length; i++) {
            const targetItem = targetList[i]

            if (!this._listeners.has(targetItem)) {
                this._listeners.set(targetItem, new Map())
            }

            const targetListeners = this._listeners.get(targetItem)!

            if (!targetListeners.has(eventType)) {
                targetListeners.set(eventType, [])
            }

            const eventTypeListeners = targetListeners.get(eventType)!

            if (
                eventTypeListeners.some(
                    (listener) => listener.callback === callback,
                )
            ) {
                const callbackName =
                    callback.name && callback.name.length > 0
                        ? callback.name
                        : 'anonymous function'

                console.warn(
                    `Listener for type ${eventType} with callback ${callbackName} already exists`,
                )
                return
            }

            const boundCallback = callback.bind(this, ...args)

            eventTypeListeners.push({ callback, options, boundCallback })

            targetItem.addEventListener(eventType, boundCallback, options)
        }
    }

    /**
     * Remove event listener(s) from target element(s)
     * @param target - Element or collection of elements to unlisten from
     * @param eventType - Optional event type to stop listening for. If not provided, all event types will be unlistened
     * @param callback - Optional event handler function to remove. If not provided, all callbacks for the event type will be removed
     */
    unlisten(
        target: Element | ArrayLike<Element>,
        eventType?: string,
        callback?: EventListener,
    ) {
        if (!target) return

        // Normalize target to an array
        const targetList = 'length' in target ? Array.from(target) : [target]

        for (let i = 0; i < targetList.length; i++) {
            const targetItem = targetList[i]

            if (!this._listeners.has(targetItem)) {
                continue
            }

            const targetListeners = this._listeners.get(targetItem)!

            targetListeners.forEach((listeners, listenerEventType) => {
                if (!eventType || listenerEventType === eventType) {
                    listeners.forEach((listener) => {
                        if (!callback || listener.callback === callback) {
                            targetItem.removeEventListener(
                                listenerEventType,
                                listener.boundCallback,
                                listener.options,
                            )
                        }
                    })

                    if (callback) {
                        const filteredListeners = listeners.filter(
                            (listener) => listener.callback !== callback,
                        )

                        if (filteredListeners.length > 0) {
                            targetListeners.set(
                                listenerEventType,
                                filteredListeners,
                            )
                        } else {
                            targetListeners.delete(listenerEventType) // Clean up empty event type entries
                        }
                    } else {
                        targetListeners.delete(listenerEventType)
                    }
                }
            })

            // Clean up empty target entries
            if (targetListeners.size === 0) {
                this._listeners.delete(targetItem)
            }
        }
    }

    /**
     * Remove all event listeners from all targets
     */
    unlistenAll() {
        this._listeners.forEach((_listeners, target) => {
            this.unlisten(target)
        })

        this._listeners.clear()
    }
}
