export default class RzColorSchemeSwitcher extends HTMLElement {
    constructor() {
        super()

        this.onCommand = this.onCommand.bind(this)
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--update-color-scheme':
                this.onButtonClick(event)
                break
        }
    }

    onButtonClick(event: CommandEvent) {
        const button = event.source as HTMLButtonElement
        const value = button.getAttribute('data-value') || undefined

        this.querySelectorAll(`button[command='${event.command}']`).forEach(
            (btn) => {
                if (btn === button) {
                    btn.setAttribute('aria-current', 'true')
                    btn.classList.add('rz-dropdown__item--selected')
                } else {
                    btn.removeAttribute('aria-current')
                    btn.classList.remove('rz-dropdown__item--selected')
                }
            },
        )

        this.updateColorScheme(value)
    }

    updateColorScheme(value: string | undefined) {
        if (!value) return
        document.documentElement.setAttribute('data-theme', value)
    }

    connectedCallback() {
        this.addEventListener('command', this.onCommand)
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
    }
}
