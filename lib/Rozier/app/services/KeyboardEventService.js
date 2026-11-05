import { KEYBOARD_EVENT_ESCAPE } from '../types/mutationTypes'

/**
 * Keyboard Event Service Listener.
 *
 * @type {Object} store
 */
export default class KeyboardEventService {
    constructor(store) {
        this.store = store
        this.init()
    }

    init() {
        this.bindEscape()
    }

    bindEscape() {
        window.addEventListener('keyup', (e) => {
            if (e.key === 'Escape' || e.keyCode === 27) {
                this.store.commit(KEYBOARD_EVENT_ESCAPE)
            }
        })
    }
}
