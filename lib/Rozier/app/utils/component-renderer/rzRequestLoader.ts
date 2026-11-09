import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-request-loader'

export type RzRequestLoaderOptions = RzElement & {
    label?: string
    active?: boolean
}

export function rzRequestLoaderRenderer(options: RzRequestLoaderOptions = {}) {
    const root = rzElement({ tag: 'rz-request-loader', ...options })
    root.classList.add(COMPONENT_CLASS_NAME)
    root.setAttribute('role', 'status')

    const bar = document.createElement('span')
    bar.classList.add(`${COMPONENT_CLASS_NAME}__bar`)
    bar.setAttribute('aria-hidden', 'true')
    root.appendChild(bar)

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`, 'rz-visually-hidden')
    label.dataset.label = options.label || 'Loading'
    root.appendChild(label)

    if (options.active) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--active`)
        label.textContent = label.dataset.label
    }

    return root
}
