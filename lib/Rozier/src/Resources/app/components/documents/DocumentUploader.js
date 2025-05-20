import Dropzone from 'dropzone'
import { fadeOut } from "../../utils/animation"
import { sleep } from '../../utils/sleep'

/**
 * Document uploader
 */
export default class DocumentUploader {
    /**
     * Constructor
     * @param {Object} options
     */
    constructor(options) {
        this.attached = false
        this.options = {
            onSuccess: (data) => {},
            onError: (data) => {},
            onAdded: (file) => {},
            url: window.RozierConfig.routes.documentsUploadPage,
            selector: '#upload-dropzone-document',
            paramName: 'form[attachment]',
            uploadMultiple: false,
            maxFilesize: 64,
            timeout: 0, // no timeout
            autoDiscover: false,
            headers: { _token: window.RozierConfig.ajaxToken },
            dictDefaultMessage: 'Drop files here to upload or click to open your explorer',
            dictFallbackMessage: "Your browser does not support drag'n'drop file uploads.",
            dictFallbackText: 'Please use the fallback form below to upload your files like in the olden days.',
            dictFileTooBig: 'File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.',
            dictInvalidFileType: "You can't upload files of this type.",
            dictResponseError: 'Server responded with {{statusCode}} code.',
            dictCancelUpload: 'Cancel upload',
            dictCancelUploadConfirmation: 'Are you sure you want to cancel this upload?',
            dictRemoveFile: 'Remove file',
            dictRemoveFileConfirmation: null,
            dictMaxFilesExceeded: 'You can not upload any more files.',
        }

        if (typeof options !== 'undefined') {
            Object.assign(this.options, options)
        }

        const optionsSelector = document.querySelector(this.options.selector)
        if (optionsSelector && !this.attached) {
            this.init()
        }
    }

    init() {
        const _self = this

        // Get folder id
        let form = document.getElementById('upload-dropzone-document')
        let dataFolderId = form.getAttribute('data-folder-id')

        if (dataFolderId && dataFolderId > 0) {
            this.options.headers.folderId = parseInt(dataFolderId)
            this.options.url = window.RozierConfig.routes.documentsUploadPage + '/' + parseInt(dataFolderId)
        }

        Dropzone.options.uploadDropzoneDocument = {
            url: this.options.url,
            method: 'post',
            headers: this.options.headers,
            paramName: this.options.paramName,
            uploadMultiple: this.options.uploadMultiple,
            timeout: this.options.timeout,
            maxFilesize: this.options.maxFilesize,
            dictDefaultMessage: this.options.dictDefaultMessage,
            dictFallbackMessage: this.options.dictFallbackMessage,
            dictFallbackText: this.options.dictFallbackText,
            dictFileTooBig: this.options.dictFileTooBig,
            dictInvalidFileType: this.options.dictInvalidFileType,
            dictResponseError: this.options.dictResponseError,
            dictCancelUpload: this.options.dictCancelUpload,
            dictCancelUploadConfirmation: this.options.dictCancelUploadConfirmation,
            dictRemoveFile: this.options.dictRemoveFile,
            dictRemoveFileConfirmation: this.options.dictRemoveFileConfirmation,
            dictMaxFilesExceeded: this.options.dictMaxFilesExceeded,
            init: function () {
                this.on('addedfile', function (file, data) {
                    _self.options.onAdded(file)
                })

                this.on('success', async function (file, data) {
                    /*
                     * Remove previews after 3 sec not
                     * to bloat the dropzone when dragging more than
                     * 20 files…
                     */
                    if (file.previewElement) {
                        let preview = file.previewElement
                        await sleep(3000)
                        await fadeOut(preview, 500)
                        preview.remove()
                    }
                    _self.options.onSuccess(data)
                    await window.Rozier.getMessages()
                })

                this.on('canceled', function (file, data) {
                    _self.options.onError(JSON.parse(data))
                    window.Rozier.getMessages()
                })

                this.on('error', function (file, errorMessage, xhr) {
                    console.error(errorMessage)
                })

                this.on('sending', function (file, xhr, formData) {
                    xhr.ontimeout = () => {
                        _self.options.onError('Server Timeout')
                        console.error('Server Timeout')
                    }
                })
            },
        }

        Dropzone.autoDiscover = this.options.autoDiscover

        try {
            /* eslint-disable no-new */
            new Dropzone(this.options.selector, Dropzone.options.uploadDropzoneDocument)

            let dropzone = document.querySelector(this.options.selector)
            dropzone.insertAdjacentHTML('beforeend', `<div class="dz-default dz-message"><span>${this.options.dictDefaultMessage}</span></div>`)
            let dzMessage = dropzone.querySelector('.dz-message')
            dzMessage.insertAdjacentHTML('beforeend' , `
            <div class="circles-icons">
                <div class="circle circle-1"></div>
                <div class="circle circle-2"></div>
                <div class="circle circle-3"></div>
                <div class="circle circle-4"></div>
                <div class="circle circle-5"></div>
                <i class="uk-icon-rz-file"></i>
            </div>`)
            this.attached = true
        } catch (e) {
            console.error(e)
        }
    }

    unbind() {}
}
