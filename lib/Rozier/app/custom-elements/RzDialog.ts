export class RzDialog extends HTMLDialogElement {
    constructor() {
        super()

        this.onOpenTargetClick = this.onOpenTargetClick.bind(this)
        this.onCloseTargetClick = this.onCloseTargetClick.bind(this)
    }

    onOpenTargetClick() {
        const isModal =
            this.hasAttribute('modal') && this.getAttribute('modal') !== 'false'
        if (isModal) {
            this.showModal()
        } else {
            this.show()
        }
    }

    onCloseTargetClick() {
        this.close()
    }

    getTargets(state: 'open' | 'close') {
        const id = this.getAttribute('id')
        const innerTargets = this.querySelectorAll(`[${state}-target]`)
        const outerTargets = document.querySelectorAll(
            `[${state}-target="${id}"]`,
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
