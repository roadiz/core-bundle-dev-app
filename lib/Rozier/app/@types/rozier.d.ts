export default class Rozier {
    onDocumentReady(): void
    getMessages(): Promise<void>
}

declare global {
    interface Window {
        Rozier: InstanceType<typeof Rozier>
    }
}
