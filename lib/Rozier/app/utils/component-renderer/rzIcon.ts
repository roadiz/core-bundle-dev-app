import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-icon'

export type RzIconOptions = RzElement & {
    class: string
}

export function rzIconRenderer(options: RzIconOptions) {
    const root = rzElement({
        tag: 'span',
        ...options,
        attributes: {
            ...options.attributes,
            class: options.class,
        },
    })

    return root
}
