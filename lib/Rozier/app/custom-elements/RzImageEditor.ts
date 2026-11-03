import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import { dataURItoBlob } from '~/utils'
import { setDocument } from '~/api/DocumentApi'

type ImageEditorAction =
    | 'drag-move'
    | 'drag-crop'
    | 'zoom-in'
    | 'zoom-out'
    | 'rotate-left'
    | 'rotate-right'
    | 'flip-horizontal'
    | 'flip-vertical'
    | 'reset'
    | 'crop'
    | 'undo'
    | 'overwrite'

interface AdjustResponse {
    message?: string
    path?: string
    errors?: Record<string, string[]>
}

const ASPECT_RATIOS: Record<string, number> = {
    '1:1': 1,
    '4:3': 4 / 3,
    '16:9': 16 / 9,
    '21:9': 21 / 9,
    '9:16': 9 / 16,
    '9:21': 9 / 21,
}

/**
 * Image editor (crop, rotate, flip, zoom) of the document "adjust" screen.
 *
 * Twig renders the whole markup (toolbar, image, hidden file form); this element only
 * drives `cropperjs` over it and posts the edited image back through the file form.
 *
 * Expected children:
 * - `[data-image]`: the `<img>` to edit
 * - `[data-action="…"]`: toolbar buttons (see `ImageEditorAction`)
 * - `[data-drag-mode="move|crop"]`: the drag mode buttons, marked selected for the current mode
 * - `[data-aspect-ratio]`: the aspect ratio `<select>` (`free` or `W:H`)
 * - `[data-state="editing"]` / `[data-state="cropped"]`: toolbar items or groups shown in each state
 * - `[data-loader]`: shown while the image loads or saves
 * - a `<form>`: the document file form, posted with the edited image
 *
 * Attributes: `filename` and `mime-type` of the document, `error-message` shown when a save
 * fails without a message from the server (network error, non-JSON response).
 *
 * Keyboard shortcuts, only while the pointer is over the editor or the focus inside it: Enter crops, Escape clears the crop box, arrows move
 * the image, C / M switch to crop / move, I / O zoom, L / R rotate, H / V flip, 0 resets
 * rotation, flip, zoom, position and crop box.
 * Ctrl/Cmd + Z restores the original image.
 */
export default class RzImageEditor extends HTMLElement {
    private image: HTMLImageElement | null = null
    private cropper: Cropper | null = null
    private cropped = false
    private saving = false
    private aspectRatio = NaN
    private originalUrl = ''
    private originalMimeType = ''
    private outputMimeType = 'image/png'

    constructor() {
        super()

        this.onClick = this.onClick.bind(this)
        this.onAspectRatioChange = this.onAspectRatioChange.bind(this)
        this.onImageLoad = this.onImageLoad.bind(this)
        this.onImageError = this.onImageError.bind(this)
        this.onKeydown = this.onKeydown.bind(this)
        this.syncDragMode = this.syncDragMode.bind(this)
    }

    connectedCallback() {
        this.image = this.querySelector<HTMLImageElement>('[data-image]')
        if (!this.image) return

        this.originalUrl = this.image.src
        this.originalMimeType = (
            this.getAttribute('mime-type') || ''
        ).toLowerCase()
        this.outputMimeType = this.resolveOutputMimeType(this.originalMimeType)

        this.addEventListener('click', this.onClick)
        // cropperjs toggles the drag mode on double click
        this.addEventListener('dblclick', this.syncDragMode)
        this.querySelector('[data-aspect-ratio]')?.addEventListener(
            'change',
            this.onAspectRatioChange,
        )
        this.image.addEventListener('load', this.onImageLoad)
        this.image.addEventListener('error', this.onImageError)
        window.addEventListener('keydown', this.onKeydown)

        this.syncStateVisibility()
        this.setLoading(true)

        // The image may have loaded, or failed, before the element upgraded (Lazyload).
        if (this.image.complete) {
            if (this.image.naturalWidth > 0) this.onImageLoad()
            else this.onImageError()
        }
    }

    disconnectedCallback() {
        this.removeEventListener('click', this.onClick)
        this.removeEventListener('dblclick', this.syncDragMode)
        this.querySelector('[data-aspect-ratio]')?.removeEventListener(
            'change',
            this.onAspectRatioChange,
        )
        this.image?.removeEventListener('load', this.onImageLoad)
        this.image?.removeEventListener('error', this.onImageError)
        window.removeEventListener('keydown', this.onKeydown)
        this.stop()
    }

    // EDITOR

    private onImageLoad() {
        // The cropped preview is a plain image: only the original one is editable.
        if (this.cropped) return

        this.start()
    }

    private onImageError() {
        this.setLoading(false)
    }

    private start() {
        if (this.cropper || !this.image) return

        this.cropper = new Cropper(this.image, {
            autoCrop: false,
            dragMode: 'move',
            background: false,
            aspectRatio: this.aspectRatio,
            viewMode: 1,
            zoomOnWheel: false,
            ready: () => {
                this.setLoading(false)
                this.syncDragMode()
            },
        })
    }

    private stop() {
        this.cropper?.destroy()
        this.cropper = null
    }

    private crop() {
        if (!this.cropper || !this.image) return

        const canvas = this.cropper.getCroppedCanvas(
            // JPEG/WebP have no alpha channel: fill transparent areas left by rotation in white
            this.outputMimeType === 'image/png' ? {} : { fillColor: '#fff' },
        )
        // null until the cropper is ready
        if (!canvas) return

        this.stop()
        this.cropped = true
        this.image.src = canvas.toDataURL(this.outputMimeType)
        this.syncStateVisibility()
    }

    private undo() {
        if (!this.cropped) return

        this.reloadOriginal()
    }

    /**
     * Shows the original image again (the saved one after an overwrite); the cropper
     * restarts once it has loaded.
     */
    private reloadOriginal() {
        if (!this.image) return

        this.cropped = false
        this.setLoading(true)
        this.image.src = this.originalUrl
        this.syncStateVisibility()
    }

    private async overwrite() {
        if (!this.cropped || !this.image || this.saving) return

        const form = this.querySelector('form')
        if (!form) return

        const formData = new FormData(form)
        formData.append(
            'form[editDocument]',
            dataURItoBlob(this.image.src),
            this.getOverwriteFilename(),
        )

        this.setLoading(true)
        this.setSaving(true)

        let data: AdjustResponse = {}
        try {
            const response = await setDocument(formData)
            data = await response.json()
        } catch (error) {
            console.error('RzImageEditor: saving the image failed', error)
        } finally {
            this.setSaving(false)
        }

        if (!data.path) {
            this.setLoading(false)
            this.toast(this.getErrorMessage(data), 'danger')
            return
        }

        if (data.message) this.toast(data.message, 'success')

        this.originalUrl = data.path
        this.reloadOriginal()
    }

    // TOOLBAR

    private onClick(event: MouseEvent) {
        const button = (event.target as Element | null)?.closest<HTMLElement>(
            '[data-action]',
        )
        if (!button || !this.contains(button)) return

        event.preventDefault()
        this.runAction(button.dataset.action as ImageEditorAction)
    }

    private onAspectRatioChange(event: Event) {
        const value = (event.target as HTMLSelectElement).value
        this.aspectRatio = ASPECT_RATIOS[value] ?? NaN
        this.cropper?.setAspectRatio(this.aspectRatio)
    }

    private runAction(action: ImageEditorAction) {
        switch (action) {
            case 'crop':
                return this.crop()
            case 'undo':
                return this.undo()
            case 'overwrite':
                return this.overwrite()
        }

        const cropper = this.cropper
        if (!cropper) return

        switch (action) {
            case 'drag-move':
                cropper.setDragMode('move')
                return this.syncDragMode()
            case 'drag-crop':
                cropper.setDragMode('crop')
                return this.syncDragMode()
            case 'zoom-in':
                return cropper.zoom(0.1)
            case 'zoom-out':
                return cropper.zoom(-0.1)
            case 'rotate-left':
                return cropper.rotate(-90)
            case 'rotate-right':
                return cropper.rotate(90)
            case 'flip-horizontal':
                return cropper.scaleX(-cropper.getData().scaleX || -1)
            case 'flip-vertical':
                return cropper.scaleY(-cropper.getData().scaleY || -1)
            case 'reset':
                cropper.reset()
                cropper.clear()
                return
        }
    }

    private onKeydown(event: KeyboardEvent) {
        // The page has other keyboard users (dialogs, drawers, scroll): stay out of their way
        // unless the editor is the one being used.
        if (this.saving) return
        if (!this.matches(':hover') && !this.contains(document.activeElement)) {
            return
        }

        const target = event.target as Element | null
        if (target?.closest('input, select, textarea, [contenteditable]')) {
            return
        }

        if (
            event.key.toLowerCase() === 'z' &&
            (event.ctrlKey || event.metaKey)
        ) {
            event.preventDefault()
            this.undo()
            return
        }

        if (this.cropped || !this.cropper) return
        if (event.ctrlKey || event.metaKey || event.altKey) return

        switch (event.key) {
            case 'Enter':
                // Enter on a focused toolbar button already clicks it.
                if (target?.closest('button, a')) return
                return this.crop()
            case 'Escape':
                this.cropper.clear()
                return
            case 'ArrowLeft':
                event.preventDefault()
                this.cropper.move(-1, 0)
                return
            case 'ArrowUp':
                event.preventDefault()
                this.cropper.move(0, -1)
                return
            case 'ArrowRight':
                event.preventDefault()
                this.cropper.move(1, 0)
                return
            case 'ArrowDown':
                event.preventDefault()
                this.cropper.move(0, 1)
                return
        }

        const shortcuts: Record<string, ImageEditorAction> = {
            c: 'drag-crop',
            m: 'drag-move',
            i: 'zoom-in',
            o: 'zoom-out',
            l: 'rotate-left',
            r: 'rotate-right',
            h: 'flip-horizontal',
            v: 'flip-vertical',
            0: 'reset',
        }
        const action = shortcuts[event.key.toLowerCase()]
        if (action) this.runAction(action)
    }

    // HELPERS

    private syncStateVisibility() {
        const state = this.cropped ? 'cropped' : 'editing'
        this.querySelectorAll<HTMLElement>('[data-state]').forEach(
            (element) => {
                element.hidden = element.dataset.state !== state
            },
        )
    }

    /**
     * Marks the drag mode button matching the cropper's current mode as selected.
     */
    private syncDragMode() {
        const mode =
            this.querySelector<HTMLElement>('.cropper-drag-box')?.dataset
                .cropperAction || 'move'

        this.querySelectorAll<HTMLElement>('[data-drag-mode]').forEach(
            (button) => {
                const isActive = button.dataset.dragMode === mode
                button.classList.toggle('rz-button--selected', isActive)
                button.setAttribute('aria-pressed', String(isActive))
            },
        )
    }

    /**
     * Locks the toolbar while a save is in flight: a second click would post the file twice,
     * and the server keeps one `original_…` copy per request.
     */
    private setSaving(isSaving: boolean) {
        this.saving = isSaving
        this.querySelectorAll<HTMLButtonElement | HTMLSelectElement>(
            '.rz-image-editor__toolbar button, .rz-image-editor__toolbar select',
        ).forEach((control) => {
            control.disabled = isSaving
        })
    }

    private setLoading(isLoading: boolean) {
        const loader = this.querySelector<HTMLElement>('[data-loader]')
        if (loader) loader.hidden = !isLoading
        this.toggleAttribute('aria-busy', isLoading)
    }

    /**
     * JPEG, PNG and WebP keep their format; anything else (GIF, unknown) is saved as PNG.
     */
    private resolveOutputMimeType(mimeType: string): string {
        return ['image/jpeg', 'image/png', 'image/webp'].includes(mimeType)
            ? mimeType
            : 'image/png'
    }

    private getOverwriteFilename(): string {
        const filename = this.getAttribute('filename') || 'image'
        if (
            this.outputMimeType !== 'image/png' ||
            this.originalMimeType === 'image/png'
        ) {
            return filename
        }

        const extensionPosition = filename.lastIndexOf('.')
        const basename =
            extensionPosition > 0
                ? filename.slice(0, extensionPosition)
                : filename

        return `${basename}.png`
    }

    private getErrorMessage(data: AdjustResponse): string {
        const messages = Object.values(data.errors || {}).flat()

        if (messages.length) return messages.join(' ')

        return data.message || this.getAttribute('error-message') || ''
    }

    private toast(message: string, status: 'success' | 'danger') {
        window.dispatchEvent(
            new CustomEvent('pushToast', { detail: { message, status } }),
        )
    }
}
