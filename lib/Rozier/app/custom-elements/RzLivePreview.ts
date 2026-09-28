import RoadizElement from '~/utils/custom-element/RoadizElement'

/*
 * <rz-live-preview> — the public website in preview mode, as a sticky column next to the
 * node-source edit form, reloaded on each save. Markup and labels are rendered by
 * RoadizRozierBundle/templates/nodes/editSource.html.twig, headless installations only.
 *
 * The column is narrower than any desktop breakpoint, so a 1:1 iframe would always render the
 * front's mobile layout. The iframe is laid out at a chosen viewport width instead and scaled
 * down to fit: the front sees a real desktop viewport, the editor sees it shrunk.
 */
const OPEN_KEY = 'rz-live-preview-open'
const WIDTH_KEY = 'rz-live-preview-width'

export default class RzLivePreview extends RoadizElement {
    private iframe: HTMLIFrameElement | null = null
    private viewport: HTMLElement | null = null
    private scaleLabel: HTMLElement | null = null
    private toggle: HTMLElement | null = null
    private widthButtons: HTMLButtonElement[] = []
    private observer: ResizeObserver | null = null
    private width = 0

    constructor() {
        super()
        this.onSaved = this.onSaved.bind(this)
        this.onMessage = this.onMessage.bind(this)
        this.fit = this.fit.bind(this)
    }

    connectedCallback() {
        this.iframe = this.querySelector('iframe')
        this.viewport = this.querySelector('.rz-live-preview__viewport')
        this.scaleLabel = this.querySelector(
            '.rz-live-preview__scale .rz-badge__label',
        )
        this.widthButtons = Array.from(
            this.querySelectorAll<HTMLButtonElement>('[data-width]'),
        )
        // The disclosure button lives in the actions menu, outside this element (aria-controls pattern).
        this.toggle = document.querySelector(`[aria-controls="${this.id}"]`)

        if (this.toggle) {
            this.listen(this.toggle, 'click', () =>
                this.setOpen(!this.hasAttribute('open')),
            )
        }
        this.listen(this.querySelectorAll('[data-close]'), 'click', () =>
            this.setOpen(false),
        )
        this.listen(this.widthButtons, 'click', (event: Event) => {
            const button = event.currentTarget as HTMLButtonElement
            this.setWidth(Number(button.dataset.width))
        })
        // RoadizElement.listen() only takes Elements: window listeners are managed by hand.
        window.addEventListener('requestAllNodeTreeChange', this.onSaved)
        window.addEventListener('message', this.onMessage)

        // The column is a share of the window: refit on resize and on its own first layout.
        this.observer = new ResizeObserver(this.fit)
        if (this.viewport) this.observer.observe(this.viewport)

        const widths = this.widthButtons.map((button) =>
            Number(button.dataset.width),
        )
        let open = false
        let width = widths[widths.length - 1] ?? 1440
        try {
            open = localStorage.getItem(OPEN_KEY) === '1'
            const stored = Number(localStorage.getItem(WIDTH_KEY))
            if (widths.includes(stored)) width = stored
        } catch {
            // private mode
        }
        this.setWidth(width)
        this.setOpen(open)
    }

    disconnectedCallback() {
        super.disconnectedCallback()
        window.removeEventListener('requestAllNodeTreeChange', this.onSaved)
        window.removeEventListener('message', this.onMessage)
        this.observer?.disconnect()
        this.observer = null
    }

    private setOpen(open: boolean) {
        this.toggleAttribute('open', open)
        this.toggle?.setAttribute('aria-expanded', String(open))
        this.toggle?.classList.toggle('uk-active', open)
        try {
            localStorage.setItem(OPEN_KEY, open ? '1' : '0')
        } catch {
            // private mode
        }
        // The front is only loaded once the column is opened.
        if (open && this.iframe && !this.iframe.src && this.dataset.src) {
            this.iframe.src = this.dataset.src
        }
        if (open) this.fit()
    }

    private setWidth(width: number) {
        this.width = width
        for (const button of this.widthButtons) {
            button.setAttribute(
                'aria-pressed',
                String(Number(button.dataset.width) === width),
            )
        }
        try {
            localStorage.setItem(WIDTH_KEY, String(width))
        } catch {
            // private mode
        }
        this.fit()
    }

    /** Lay the iframe out at the chosen viewport width, then scale it down to the column. */
    private fit() {
        // The observer fires once on observe(), before setWidth() has run.
        const available = this.viewport?.clientWidth
        if (!available || !this.width || !this.iframe || !this.viewport) return

        const scale = Math.min(1, available / this.width)
        this.iframe.style.width = `${this.width}px`
        this.iframe.style.height = `${this.viewport.clientHeight / scale}px`
        this.iframe.style.transform = `scale(${scale})`
        if (this.scaleLabel) {
            this.scaleLabel.textContent = `${Math.round(scale * 100)} %`
        }
    }

    private onSaved() {
        // NodeSourceEditPage.onFormSubmit() posts the form in place (no DOM replacement) and dispatches
        // this on success *and* on validation errors — Rozier has no success-only event yet.
        // An extra reload is harmless.
        if (this.iframe?.src && this.dataset.src)
            this.iframe.src = this.dataset.src
    }

    /** The front says hello once hydrated: a message sent on iframe `load` would race its own listener. */
    private onMessage(event: MessageEvent) {
        const origin = this.dataset.frontOrigin
        if (
            !origin ||
            event.origin !== origin ||
            event.source !== this.iframe?.contentWindow
        ) {
            return
        }
        if (event.data?.type !== 'roadiz:ready' || !this.dataset.nodeSourceId) {
            return
        }

        ;(event.source as Window).postMessage(
            {
                type: 'roadiz:inspect',
                nodeSourceId: this.dataset.nodeSourceId,
            },
            origin,
        )
    }
}
