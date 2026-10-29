import type Lazyload from '~/Lazyload'
import type VueApp from '~/App'

export default class Rozier {
    windowWidth: number | null
    windowHeight: number | null
    resizeFirst: boolean
    canvasLoader: CanvasLoader | null
    lazyload: Lazyload
    vueApp: VueApp
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
    interface CanvasLoader {
        setColor(color: string): void
        setShape(shape: string): void
        setDensity(density: number): void
        setRange(range: number): void
        setSpeed(speed: number): void
        setFPS(fps: number): void
        show(): void
        hide(): void
    }

    interface HTMLElement {
        setHTMLUnsafe(value: string): void
    }

    interface Window {
        Rozier?: InstanceType<typeof Rozier>
        CanvasLoader: { new (containerId: string): CanvasLoader }
    }
}
