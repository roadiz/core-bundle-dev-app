import type Lazyload from '~/Lazyload'

export default class Rozier {
    windowWidth: number | null
    windowHeight: number | null
    resizeFirst: boolean
    lazyload: Lazyload
    nodeStatuses?: unknown

    onDocumentReady(): void
    bindMainTrees(): void
    refreshMainNodeTree(translationId?: string): Promise<void> | void
    refreshMainTagTree(translationId?: string): Promise<void> | void
    refreshMainFolderTree(translationId?: string): Promise<void> | void
    getMessages(): Promise<void>
    resize(): void
}

declare global {
    interface HTMLElement {
        setHTMLUnsafe(value: string): void
    }

    interface Window {
        Rozier?: InstanceType<typeof Rozier>
    }
}
