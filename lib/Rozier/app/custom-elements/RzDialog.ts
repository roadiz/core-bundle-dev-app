export class RzDialog extends HTMLDialogElement {
    constructor() {
        super()

        this.display = this.display.bind(this)
        this.hide = this.hide.bind(this)
    }

    static get observedAttributes() {
        return []
    }

    display() {
        const isNonModal = this.hasAttribute('rz-dialog-non-modal')
        if (isNonModal) {
            this.show()
        } else {
            this.showModal()
        }
    }

    hide() {
        this.close()
    }

    attributeChangedCallback() {}

    getTargets(state: 'open' | 'close') {
        const id = this.getAttribute('id')
        const innerTargets = this.querySelectorAll(
            `[rz-dialog-${state}-target]`,
        )
        const outerTargets = document.querySelectorAll(
            `[rz-dialog-${state}-target="${id}"]`,
        )

        return [
            ...Array.from(innerTargets),
            ...Array.from(outerTargets),
        ].filter((el) => el)
    }

    connectedCallback() {
        const openTargets = this.getTargets('open')
        openTargets?.forEach((target) => {
            target.addEventListener('click', this.display)
        })

        const closeTargets = this.getTargets('close')
        closeTargets?.forEach((target) => {
            target.addEventListener('click', this.hide)
        })
    }

    disconnectedCallback() {
        const openTargets = this.getTargets('open')
        openTargets?.forEach((target) =>
            target.removeEventListener('click', this.display),
        )
        const closeTargets = this.getTargets('close')
        closeTargets?.forEach((target) =>
            target.removeEventListener('click', this.hide),
        )
    }
}
