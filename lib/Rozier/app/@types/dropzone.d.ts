declare module 'dropzone' {
    export interface DropzoneFile extends File {
        previewElement?: HTMLElement
        status?: string
        upload?: {
            progress: number
            total: number
            bytesSent: number
        }
    }

    export interface DropzoneOptions {
        url?: string
        method?: string
        paramName?: string
        uploadMultiple?: boolean
        maxFilesize?: number
        timeout?: number
        autoDiscover?: boolean
        headers?: Record<string, string>
        dictDefaultMessage?: string
        dictFallbackMessage?: string
        dictFallbackText?: string
        dictFileTooBig?: string
        dictInvalidFileType?: string
        dictResponseError?: string
        dictCancelUpload?: string
        dictCancelUploadConfirmation?: string
        dictRemoveFile?: string
        dictMaxFilesExceeded?: string
        acceptedFiles?: string
        addRemoveLinks?: boolean
        previewsContainer?: string | HTMLElement
        clickable?: boolean | string | HTMLElement | HTMLElement[]
        maxFiles?: number
        parallelUploads?: number
        resizeWidth?: number
        resizeHeight?: number
        resizeMethod?: 'contain' | 'crop'
        thumbnailWidth?: number
        thumbnailHeight?: number
        thumbnailMethod?: 'contain' | 'crop'
        createImageThumbnails?: boolean
        maxThumbnailFilesize?: number
        ignoreHiddenFiles?: boolean
        accept?: (
            file: DropzoneFile,
            done: (error?: string | Error) => void,
        ) => void
        init?: () => void
        forceFallback?: boolean
        fallback?: () => void
        resize?: (
            file: DropzoneFile,
            width: number,
            height: number,
            resizeMethod: string,
        ) => unknown
        drop?: (e: DragEvent) => void
        dragstart?: (e: DragEvent) => void
        dragend?: (e: DragEvent) => void
        dragenter?: (e: DragEvent) => void
        dragover?: (e: DragEvent) => void
        dragleave?: (e: DragEvent) => void
    }

    class Dropzone {
        static autoDiscover: boolean
        static options: DropzoneOptions
        static confirm: (
            message: string,
            accepted: () => void,
            rejected?: () => void,
        ) => void

        constructor(element: string | HTMLElement, options?: DropzoneOptions)

        on(event: 'addedfile', callback: (file: DropzoneFile) => void): void
        on(
            event: 'success',
            callback: (file: DropzoneFile, response: unknown) => void,
        ): void
        on(
            event: 'error',
            callback: (
                file: DropzoneFile,
                errorMessage: string | Error,
            ) => void,
        ): void
        on(
            event: 'canceled',
            callback: (file: DropzoneFile, data?: unknown) => void,
        ): void
        on(
            event: 'sending',
            callback: (
                file: DropzoneFile,
                xhr: XMLHttpRequest,
                formData: FormData,
            ) => void,
        ): void
        on(event: 'complete', callback: (file: DropzoneFile) => void): void
        on(
            event: 'uploadprogress',
            callback: (
                file: DropzoneFile,
                progress: number,
                bytesSent: number,
            ) => void,
        ): void
        on(
            event: 'totaluploadprogress',
            callback: (
                totalProgress: number,
                totalBytes: number,
                totalBytesSent: number,
            ) => void,
        ): void
        on(event: 'removedfile', callback: (file: DropzoneFile) => void): void
        on(
            event: 'thumbnail',
            callback: (file: DropzoneFile, dataUrl: string) => void,
        ): void
        on(
            event: 'maxfilesexceeded',
            callback: (file: DropzoneFile) => void,
        ): void
        on(
            event: 'maxfilesreached',
            callback: (files: DropzoneFile[]) => void,
        ): void
        on(event: 'queuecomplete', callback: () => void): void
        on(event: string, callback: (...args: unknown[]) => void): void

        off(event?: string, callback?: (...args: unknown[]) => void): void
        emit(event: string, ...args: unknown[]): void

        destroy(): void
        disable(): void
        enable(): void
        removeFile(file: DropzoneFile): void
        removeAllFiles(cancelIfNecessary?: boolean): void
        processQueue(): void
        cancelUpload(file: DropzoneFile): void
        createThumbnail(
            file: DropzoneFile,
            callback?: (dataUrl: string) => void,
        ): void
        createThumbnailFromUrl(
            file: DropzoneFile,
            imageUrl: string,
            callback?: () => void,
        ): void
        accept(file: DropzoneFile, done: (error?: string | Error) => void): void
        addFile(file: DropzoneFile): void
        enqueueFile(file: DropzoneFile): void
        uploadFile(file: DropzoneFile): void
        uploadFiles(files: DropzoneFile[]): void
        getActiveFiles(): DropzoneFile[]
        getAddedFiles(): DropzoneFile[]
        getRejectedFiles(): DropzoneFile[]
        getFilesWithStatus(status: string): DropzoneFile[]
        getQueuedFiles(): DropzoneFile[]
        getUploadingFiles(): DropzoneFile[]
        getAcceptedFiles(): DropzoneFile[]

        options: DropzoneOptions
        element: HTMLElement
        files: DropzoneFile[]
        defaultOptions: DropzoneOptions
    }

    export default Dropzone
}
