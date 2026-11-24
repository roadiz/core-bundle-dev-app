import { toKebabCase } from '~/utils/string/toKebabCase'
import type { ElementModule } from '~/utils/custom-element/defineLazyElement'

const modules = import.meta.glob<ElementModule>('./custom-elements/*.{js,ts}')
const customElements: Record<string, () => Promise<ElementModule>> = {}

for (const path in modules) {
    const fileName = path.match(/\/([^/]+)\.(js|ts)$/)[1] // MyElement or my-element
    const tagName = toKebabCase(fileName)

    customElements[tagName] = modules[path]
}

export default customElements
