export class RzDialog extends HTMLDialogElement {
    constructor() {
        super()

        this.onOpenTargetClick = this.onOpenTargetClick.bind(this)
        this.onCloseTargetClick = this.onCloseTargetClick.bind(this)
        this.onToggleTargetClick = this.onToggleTargetClick.bind(this)
    }

    showDialog() {
        const isModal =
            this.hasAttribute('modal') && this.getAttribute('modal') !== 'false'

        if (isModal) {
            this.showModal()
        } else {
            this.show()
        }
    }

    onOpenTargetClick() {
        this.showDialog()
    }

    onToggleTargetClick() {
        if (this.open) {
            this.close()
        } else {
            this.showDialog()
        }
    }

    onCloseTargetClick() {
        this.close()
    }

    getTargets(action: 'open' | 'close' | 'toggle') {
        const id = this.getAttribute('id')
        const attributeName = `${action}target`

        const innerTargets =
            action === 'open' ? [] : this.querySelectorAll(`[${attributeName}]`)
        const outerTargets = document.querySelectorAll(
            `[${attributeName}="${id}"]`,
        )

        return [
            ...Array.from(innerTargets),
            ...Array.from(outerTargets),
        ].filter((el) => el)
    }

    connectedCallback() {
        if (this.getAttribute('defaultopen') === 'true') {
            this.showDialog()
        }
        const openTargets = this.getTargets('open')
        openTargets?.forEach((target) => {
            target.addEventListener('click', this.onOpenTargetClick)
        })

        const toggleTargets = this.getTargets('toggle')
        toggleTargets?.forEach((target) => {
            target.addEventListener('click', this.onToggleTargetClick)
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

        const toggleTargets = this.getTargets('toggle')
        toggleTargets?.forEach((target) => {
            target.removeEventListener('click', this.onToggleTargetClick)
        })
        const closeTargets = this.getTargets('close')
        closeTargets?.forEach((target) =>
            target.removeEventListener('click', this.onCloseTargetClick),
        )
    }
}
