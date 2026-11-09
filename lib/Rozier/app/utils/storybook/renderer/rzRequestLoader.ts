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

    const labelText = options.label || 'Loading'

    // The pill text never changes, the status label below carries the announcement
    const pill = document.createElement('span')
    pill.classList.add(`${COMPONENT_CLASS_NAME}__pill`)
    pill.setAttribute('aria-hidden', 'true')
    root.appendChild(pill)

    const spinner = document.createElement('span')
    spinner.classList.add('rz-spinner')
    pill.appendChild(spinner)

    const text = document.createElement('span')
    text.classList.add(`${COMPONENT_CLASS_NAME}__text`)
    text.textContent = labelText
    pill.appendChild(text)

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`, 'rz-visually-hidden')
    label.dataset.label = labelText
    root.appendChild(label)

    if (options.active) {
        root.classList.add(
            `${COMPONENT_CLASS_NAME}--active`,
            `${COMPONENT_CLASS_NAME}--slow`,
        )
        label.textContent = labelText
    }

    return root
}
