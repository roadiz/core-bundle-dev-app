export default class RzColorSchemeSwitcher extends HTMLElement {
    private readonly storageKey = 'rz-color-scheme'

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

        this.updateSelectedButton(event.command, value)

        this.updateColorScheme(value)
    }

    updateColorScheme(value: string | undefined) {
        if (!value || value === 'system') {
            document.documentElement.removeAttribute('data-theme')
            localStorage.removeItem(this.storageKey)
            return
        }

        document.documentElement.setAttribute('data-theme', value)
        localStorage.setItem(this.storageKey, value)
    }

    updateSelectedButton(command: string, value: string | null | undefined) {
        this.querySelectorAll(`button[command='${command}']`).forEach((btn) => {
            if (btn.getAttribute('data-value') === value) {
                btn.setAttribute('aria-current', 'true')
                btn.classList.add('rz-dropdown__item--selected')
            } else {
                btn.removeAttribute('aria-current')
                btn.classList.remove('rz-dropdown__item--selected')
            }
        })
    }

    connectedCallback() {
        this.addEventListener('command', this.onCommand)

        const storedValue = localStorage.getItem(this.storageKey)
        if (storedValue === 'light' || storedValue === 'dark') {
            this.updateSelectedButton('--update-color-scheme', storedValue)
            this.updateColorScheme(storedValue)
        }
    }

    disconnectedCallback() {
        this.removeEventListener('command', this.onCommand)
    }
}
