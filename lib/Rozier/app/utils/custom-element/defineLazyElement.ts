export type ElementModule = { default: CustomElementConstructor } | Record<string, unknown>

/**
 * Define a custom element that will be lazy-loaded when first used.
 *
 * @param tagName - The custom element tag name.
 * @param loader - A function returning a dynamic import() Promise.
 */
export function defineLazyElement(tagName: string, loader: () => Promise<ElementModule>): void {
  const existing = document.querySelector(tagName) // Check if element already exists in DOM

  let loadingPromise: Promise<void> | null = null
  let observer: MutationObserver | null = null

  async function load() {
    if (!loadingPromise) {
      loadingPromise = loader().then((module) => {
        const elementConstructor = (module.default || Object.values(module)[0]) as
          | CustomElementConstructor
          | undefined

        observer?.disconnect()
        observer = null

        if (!elementConstructor) {
          throw new Error(`No element class found for <${tagName}>`)
        }

        customElements.define(tagName, elementConstructor)
      })
    }

    await loadingPromise
  }

  if (existing) {
    load()
  }
  else {
    observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (node instanceof Element) {
            if (node.tagName.toLowerCase() === tagName) {
              load()
            }
            else {
              const found = node.querySelector(tagName)
              if (found) load()
            }
          }
        }
      }
    })

    observer.observe(document, { childList: true, subtree: true })
  }
}
