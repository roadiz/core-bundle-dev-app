export class RzDrawer extends HTMLElement {
    fileUploadIsVisible: boolean = false
    fileUpload: HTMLElement | null = null

    constructor() {
        super()

        this.onCommand = this.onCommand.bind(this)
    }

    connectedCallback() {
        this.fileUpload = this.querySelector('rz-file-upload')

        this.addEventListener('command', this.onCommand)
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
    }

    showFileUpload() {
        if (this.fileUploadIsVisible) {
            return
        }

        this.fileUploadIsVisible = true

        if (!this.fileUpload) {
            return
        }

        this.fileUpload.removeAttribute('hidden')

        this.updateFileUploadControls()
    }

    hideFileUpload() {
        if (!this.fileUploadIsVisible) {
            return
        }

        this.fileUploadIsVisible = false

        if (!this.fileUpload) {
            return
        }

        this.fileUpload.setAttribute('hidden', '')

        this.updateFileUploadControls()
    }

    updateFileUploadControls() {
        if (!this.fileUpload) {
            return
        }

        const controls = document.querySelectorAll(
            `[aria-controls="${this.fileUpload.id}"]`,
        )

        controls.forEach((button) => {
            button.setAttribute(
                'aria-expanded',
                this.fileUploadIsVisible ? 'true' : 'false',
            )
        })
    }

    toggleFileUpload() {
        if (this.fileUploadIsVisible) {
            this.hideFileUpload()
        } else {
            this.showFileUpload()
        }
    }

    onCommand(event: CommandEvent) {
        if (event.command === '--toggle-file-upload') {
            this.toggleFileUpload()
        }
    }
}
