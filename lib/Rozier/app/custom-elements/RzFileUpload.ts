import Dropzone, { type DropzoneOptions } from 'dropzone'
import { fadeOut } from '~/utils/animation'
import { sleep } from '~/utils/sleep'

export default class RzFileUpload extends HTMLElement {
    options: DropzoneOptions
    dropzone: Dropzone | null = null

    constructor() {
        super()

        this.options = {
            ...window.RozierConfig?.messages?.dropzone,
            url:
                this.getAttribute('url') ||
                window.RozierConfig.routes?.documentsUploadPage,
            paramName: 'form[attachment]',
            uploadMultiple: false,
            maxFilesize: 64,
            timeout: 0, // no timeout
            autoDiscover: false,
            headers: { _token: window.RozierConfig?.ajaxToken || '' },
        }
    }

    connectedCallback() {
        this.dropzone = new Dropzone(this, this.options)

        this.dropzone.on('addedfile', (file) => {
            this.dispatchEvent(
                new CustomEvent('addedfile', { detail: { file } }),
            )
        })

        this.dropzone.on('success', async (file, response) => {
            this.dispatchEvent(
                new CustomEvent('success', { detail: { file, response } }),
            )

            if (file.previewElement) {
                const preview = file.previewElement
                await sleep(3000)
                await fadeOut(preview, 500)
                preview.remove()
            }

            window.Rozier.getMessages()
        })

        this.dropzone.on('canceled', (file, data) => {
            this.dispatchEvent(
                new CustomEvent('canceled', { detail: { file, data } }),
            )

            window.Rozier.getMessages()
        })

        this.dropzone.on('error', (file, errorMessage) => {
            this.dispatchEvent(
                new CustomEvent('error', {
                    detail: { file, errorMessage },
                }),
            )
        })

        this.dropzone.on('sending', (file, xhr) => {
            this.dispatchEvent(
                new CustomEvent('sending', { detail: { file, xhr } }),
            )

            xhr.ontimeout = () => {
                this.dispatchEvent(
                    new CustomEvent('timeout', { detail: { file, xhr } }),
                )
            }
        })

        this.insertAdjacentHTML(
            'beforeend',
            `<div class="dz-default dz-message"><span>${this.options.dictDefaultMessage}</span></div>`,
        )
        const dzMessage = this.querySelector('.dz-message')

        if (dzMessage) {
            dzMessage.insertAdjacentHTML(
                'beforeend',
                `
                    <div class="circles-icons">
                        <div class="circle circle-1"></div>
                        <div class="circle circle-2"></div>
                        <div class="circle circle-3"></div>
                        <div class="circle circle-4"></div>
                        <div class="circle circle-5"></div>
                        <i class="uk-icon-rz-file"></i>
                    </div>`,
            )
        }
    }

    disconnectedCallback() {
        if (this.dropzone !== null) {
            this.dropzone.destroy()
            this.dropzone = null
        }
    }
}
