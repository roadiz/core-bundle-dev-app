import { debounce } from 'lodash'

export const ACTIVE_CLASS_NAME = 'rz-request-loader--active'
export const SLOW_CLASS_NAME = 'rz-request-loader--slow'

/** A request shorter than this shows the bar only: no pill, no announcement. */
export const SLOW_DELAY_MS = 500
/** Once shown, the pill stays at least this long, so it never flashes. */
export const SLOW_MIN_DURATION_MS = 600

/**
 * Global loader shown while an AJAX navigation or a long action runs.
 * Any module drives it through the `requestLoaderShow` / `requestLoaderHide` window events.
 */
export default class RzRequestLoader extends HTMLElement {
    private hideTimeoutId: number | null = null
    private pillShownAt: number | null = null

    // Debounced: each requestLoaderShow restarts the delay
    private showPill = debounce(() => {
        this.pillShownAt = performance.now()
        this.classList.add(SLOW_CLASS_NAME)
        this.setAnnouncement(this.labelElement?.dataset.label || '')
    }, SLOW_DELAY_MS)

    constructor() {
        super()

        this.show = this.show.bind(this)
        this.hide = this.hide.bind(this)
    }

    get labelElement() {
        return this.querySelector<HTMLElement>('.rz-request-loader__label')
    }

    connectedCallback() {
        window.addEventListener('requestLoaderShow', this.show)
        window.addEventListener('requestLoaderHide', this.hide)
    }

    disconnectedCallback() {
        window.removeEventListener('requestLoaderShow', this.show)
        window.removeEventListener('requestLoaderHide', this.hide)
        this.showPill.cancel()
        this.clearHideTimeout()
    }

    show() {
        // A new request during the minimum duration keeps the pill on
        this.clearHideTimeout()
        this.classList.add(ACTIVE_CLASS_NAME)

        if (this.pillShownAt === null) {
            this.showPill()
        }
    }

    hide() {
        this.showPill.cancel()

        const remaining =
            this.pillShownAt === null
                ? 0
                : SLOW_MIN_DURATION_MS - (performance.now() - this.pillShownAt)

        if (remaining <= 0) {
            this.reset()
            return
        }

        this.clearHideTimeout()
        this.hideTimeoutId = window.setTimeout(() => this.reset(), remaining)
    }

    private reset() {
        this.hideTimeoutId = null
        this.pillShownAt = null
        this.classList.remove(ACTIVE_CLASS_NAME, SLOW_CLASS_NAME)
        this.setAnnouncement('')
    }

    /**
     * The status region announces text inserted while it is in the DOM.
     * The label is visually hidden: changing it never resizes the pill.
     */
    private setAnnouncement(text: string) {
        const label = this.labelElement
        if (label) {
            label.textContent = text
        }
    }

    private clearHideTimeout() {
        if (this.hideTimeoutId !== null) {
            window.clearTimeout(this.hideTimeoutId)
            this.hideTimeoutId = null
        }
    }
}
