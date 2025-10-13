export type ElementModule =
    | { default: CustomElementConstructor }
    | Record<string, unknown>

/**
 * Define a custom element that will be lazy-loaded when first used.
 *
 * @param name - The custom element name (tag name or is attribute).
 * @param loader - A function returning a dynamic import() Promise.
 */
export function defineLazyElement(
    name: string,
    loader: () => Promise<ElementModule>,
): void {
    const selector = `${name}, [is="${name}"]` // Selector for both autonomous and customized built-in elements
    const element = document.querySelector(selector) // Check if element already exists in DOM

    let loadingPromise: Promise<void> | null = null
    let observer: MutationObserver | null = null

    async function load(tagName: string) {
        if (!loadingPromise) {
            loadingPromise = loader().then((module) => {
                const elementConstructor = (module.default ||
                    Object.values(module)[0]) as
                    | CustomElementConstructor
                    | undefined

                observer?.disconnect()
                observer = null

                if (!elementConstructor) {
                    throw new Error(`No element class found for <${name}>`)
                }

                const options =
                    tagName === name ? undefined : { extends: tagName }

                customElements.define(name, elementConstructor, options)
            })
        }

        await loadingPromise
    }

    if (element) {
        load(element.tagName.toLowerCase())
    } else {
        observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (node instanceof Element) {
                        const tagName = node.tagName.toLowerCase()

                        if (
                            tagName === name ||
                            node.getAttribute('is') === name
                        ) {
                            load(tagName)
                        } else {
                            const childElement = node.querySelector(selector)

                            if (childElement)
                                load(childElement.tagName.toLowerCase())
                        }
                    }
                }
            }
        })

        observer.observe(document, { childList: true, subtree: true })
    }
}
