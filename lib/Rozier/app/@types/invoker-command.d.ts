declare global {
    // Pretty new API, not yet in TypeScript
    // https://developer.mozilla.org/en-US/docs/Web/API/CommandEvent
    interface CommandEvent extends Event {
        readonly command: string
        readonly source: HTMLElement | null
    }
}

export {}
