import {addClass} from '../utils/plugins'

/**
 * Node Tree
 */
export default class NodeTree {
    constructor() {
        // Selectors
        this.content = document.querySelector('.content-node-tree')
        this.$dropdown = null

        // Methods
        if (this.content) {
            this.$dropdown = this.content.querySelectorAll('.uk-dropdown-small')
            this.init()
        }
    }

    /**
     * Init
     */
    init() {
        this.contentHeight = this.content.offsetHeight
        if (this.contentHeight >= window.Rozier.windowHeight - 400) this.dropdownFlip()
    }

    unbind() {}

    /**
     * Flip dropdown
     */
    dropdownFlip() {
        for (let i = this.$dropdown.length - 1; i >= this.$dropdown.length - 3; i--) {
            addClass(this.$dropdown[i], 'uk-dropdown-up')
        }
    }
}
