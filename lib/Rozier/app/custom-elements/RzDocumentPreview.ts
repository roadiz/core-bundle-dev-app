import type { RzDrawerItem } from '~/utils/drawer/types'

export const SHOW_PREVIEW_EVENT = 'show-preview'

export type ShowPreviewDetail = {
    document?: Pick<
        RzDrawerItem,
        | 'previewHtml'
        | 'classname'
        | 'displayable'
        | 'embedPlatform'
        | 'isPdf'
        | 'isImage'
        | 'isVideo'
        | 'isSvg'
    >
}

/**
 * Document preview dialog, placed once in the shared layout.
 *
 * Twig renders the shell (dialog, information bar, close button); this element fills it with
 * the document's preview HTML when asked to, and empties it on close (stops a playing video).
 *
 * Contract: dispatch `show-preview` on `document` with `detail.document` (`ShowPreviewDetail`).
 * The emitter knows nothing about how the preview renders.
 *
 * Expected children:
 * - a `<dialog>`: shown as a modal, so Escape closes it natively
 * - `[data-info="classname|displayable|embedPlatform"]`: filled with the document's values,
 *   hidden when empty (`displayable` too when it equals `classname`)
 * - `[data-preview]`: receives the preview HTML
 * - `[data-close]`: closes the preview
 *
 * Closes on Escape, on the close button, on a click outside the preview (backdrop) and on page change.
 */
export default class RzDocumentPreview extends HTMLElement {
    constructor() {
        super()

        this.onShowPreview = this.onShowPreview.bind(this)
        this.onDialogClick = this.onDialogClick.bind(this)
        this.onDialogClose = this.onDialogClose.bind(this)
        this.close = this.close.bind(this)
    }

    get dialog() {
        return this.querySelector('dialog')
    }

    get preview() {
        return this.querySelector<HTMLElement>('[data-preview]')
    }

    onShowPreview(event: Event) {
        const document = (event as CustomEvent<ShowPreviewDetail>).detail
            ?.document

        if (!document) return

        this.open(document)
    }

    open(document: NonNullable<ShowPreviewDetail['document']>) {
        const dialog = this.dialog
        const preview = this.preview
        if (!dialog || !preview) return

        this.setInfo('classname', document.classname)
        this.setInfo(
            'displayable',
            document.displayable !== document.classname
                ? document.displayable
                : null,
        )
        this.setInfo('embedPlatform', document.embedPlatform)

        preview.classList.toggle(
            'rz-document-preview__container--pdf',
            !!document.isPdf,
        )
        preview.classList.toggle(
            'rz-document-preview__container--no-preview',
            !document.isImage,
        )
        preview.classList.toggle(
            'rz-document-preview__container--video',
            !!document.isVideo,
        )
        preview.classList.toggle(
            'rz-document-preview__container--svg',
            !!document.isSvg,
        )
        preview.innerHTML = document.previewHtml || ''

        if (!dialog.open) dialog.showModal()
    }

    close() {
        this.dialog?.close()
    }

    setInfo(name: string, value?: string | null) {
        const element = this.querySelector<HTMLElement>(`[data-info="${name}"]`)
        if (!element) return

        element.textContent = value || ''
        element.hidden = !value
    }

    // A click on the dialog itself (not on its content) is a click on the backdrop
    onDialogClick(event: MouseEvent) {
        if (event.target === this.dialog) this.close()
    }

    // `close` fires asynchronously: skip it when the preview was reopened in the meantime
    onDialogClose() {
        if (this.dialog?.open || !this.preview) return

        this.preview.innerHTML = ''
    }

    connectedCallback() {
        document.addEventListener(SHOW_PREVIEW_EVENT, this.onShowPreview)
        window.addEventListener('pagechange', this.close)
        this.dialog?.addEventListener('click', this.onDialogClick)
        this.dialog?.addEventListener('close', this.onDialogClose)
        this.querySelectorAll('[data-close]').forEach((button) => {
            button.addEventListener('click', this.close)
        })
    }

    disconnectedCallback() {
        document.removeEventListener(SHOW_PREVIEW_EVENT, this.onShowPreview)
        window.removeEventListener('pagechange', this.close)
        this.dialog?.removeEventListener('click', this.onDialogClick)
        this.dialog?.removeEventListener('close', this.onDialogClose)
        this.querySelectorAll('[data-close]').forEach((button) => {
            button.removeEventListener('click', this.close)
        })
    }
}
