import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-progress'

export const STATES = ['warning', 'error'] as const

export type RzProgressOptions = RzElement & {
    label?: string
    valueLabel?: string
    hint?: string
    value?: number
    max?: number
    projection?: number
    state?: (typeof STATES)[number]
    stat?: boolean
}

export function rzProgressRenderer(options: RzProgressOptions) {
    const max = options.max ?? 100
    const value = Math.min(max, options.value ?? 0)
    const percentage = max > 0 ? (value / max) * 100 : 0
    const projection =
        options.projection && max > 0
            ? Math.min(100 - percentage, (options.projection / max) * 100)
            : 0

    const root = rzElement({ tag: 'div', ...options })
    root.classList.add(COMPONENT_CLASS_NAME)

    if (options.state) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--${options.state}`)
    }
    if (options.stat) {
        root.classList.add(`${COMPONENT_CLASS_NAME}--stat`)
    }

    if (options.label) {
        const header = document.createElement('div')
        header.classList.add(`${COMPONENT_CLASS_NAME}__header`)

        const label = document.createElement('span')
        label.textContent = options.label
        header.appendChild(label)

        const valueLabel = document.createElement('span')
        valueLabel.classList.add(`${COMPONENT_CLASS_NAME}__value`)
        valueLabel.textContent =
            options.valueLabel ?? `${Math.round(percentage)} %`
        header.appendChild(valueLabel)

        root.appendChild(header)
    }

    const track = document.createElement('div')
    track.classList.add(`${COMPONENT_CLASS_NAME}__track`)

    const bar = document.createElement('progress')
    bar.classList.add(`${COMPONENT_CLASS_NAME}__bar`)
    bar.max = max
    bar.value = value
    track.appendChild(bar)

    if (projection > 0) {
        const projectionElement = document.createElement('span')
        projectionElement.classList.add(`${COMPONENT_CLASS_NAME}__projection`)
        projectionElement.setAttribute('aria-hidden', 'true')
        projectionElement.style.setProperty(
            '--rz-progress-projection-start',
            `${percentage.toFixed(1)}%`,
        )
        projectionElement.style.setProperty(
            '--rz-progress-projection-width',
            `${projection.toFixed(1)}%`,
        )
        track.appendChild(projectionElement)
    }

    root.appendChild(track)

    if (options.hint) {
        const hint = document.createElement('p')
        hint.classList.add(`${COMPONENT_CLASS_NAME}__hint`)
        hint.textContent = options.hint
        root.appendChild(hint)
    }

    return root
}
