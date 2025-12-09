export class RzDialog extends HTMLDialogElement {
    constructor() {
        super()

        this.onOpenTargetClick = this.onOpenTargetClick.bind(this)
        this.onCloseTargetClick = this.onCloseTargetClick.bind(this)
    }

    static get observedAttributes() {
        return []
    }

    onOpenTargetClick() {
        const isNonModal = this.hasAttribute('rz-dialog-non-modal')
        if (isNonModal) {
            this.show()
        } else {
            this.showModal()
        }
    }

    onCloseTargetClick() {
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
            target.addEventListener('click', this.onOpenTargetClick)
        })

        const closeTargets = this.getTargets('close')
        closeTargets?.forEach((target) => {
            target.addEventListener('click', this.onCloseTargetClick)
        })
    }

    disconnectedCallback() {
        const openTargets = this.getTargets('open')
        openTargets?.forEach((target) =>
            target.removeEventListener('click', this.onOpenTargetClick),
        )
        const closeTargets = this.getTargets('close')
        closeTargets?.forEach((target) =>
            target.removeEventListener('click', this.onCloseTargetClick),
        )
    }
}
