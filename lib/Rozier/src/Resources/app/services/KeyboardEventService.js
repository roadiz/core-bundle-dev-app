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
        window.Mousetrap.bind('esc', () => this.store.commit(KEYBOARD_EVENT_ESCAPE))
    }

    bindSpace() {
        window.Mousetrap.bind('space', (e) => {
            if (e.preventDefault && this.store.getters.documentPreviewGetDocument) {
                e.preventDefault()
            } else if (this.store.getters.documentPreviewGetDocument) {
                // internet explorer
                e.returnValue = false
            }

            if (e.target === document.body && e.preventDefault) {
                e.preventDefault()
            } else if (e.target === document.body) {
                // internet explorer
                e.returnValue = false
            }

            this.store.commit(KEYBOARD_EVENT_SPACE)
        })
    }
}
