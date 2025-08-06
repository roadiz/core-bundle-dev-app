import { KEYBOARD_EVENT_ESCAPE, KEYBOARD_EVENT_SPACE } from '../types/mutationTypes'

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
        this.bindSpace()
    }

    bindEscape() {
        window.addEventListener('keyup', (e) => {
            if (e.key === 'Escape' || e.keyCode === 27) {
                this.store.commit(KEYBOARD_EVENT_ESCAPE)
            }
        })
    }

    bindSpace() {
        window.addEventListener('keyup', (e) => {
            if (!e.key === 'Space' || !this.store.getters.documentPreviewGetDocument) {
                return
            }
            e.preventDefault()
            this.store.commit(KEYBOARD_EVENT_SPACE, { event: e })

            return false
        })
    }
}
