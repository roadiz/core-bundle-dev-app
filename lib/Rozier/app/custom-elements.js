import { toKebabCase } from '~/utils/string/toKebabCase'

const modules = import.meta.glob('./custom-elements/*.{js,ts}')
const customElements = {}

for (const path in modules) {
  const fileName = path.match(/\/([^/]+)\.(js|ts)$/)[1] // MyElement or my-element
  const tagName = toKebabCase(fileName)

  customElements[tagName] = modules[path]
}

export default customElements
