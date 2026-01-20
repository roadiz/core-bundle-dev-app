export class RzPage extends HTMLElement {
    constructor() {
        super()

        this.onCommand = this.onCommand.bind(this)
    }

    scrollToTop() {
        this.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--scroll-top':
                this.scrollToTop()
                break
        }
    }

    connectedCallback() {
        this.addEventListener('command', this.onCommand)
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
    }
}
