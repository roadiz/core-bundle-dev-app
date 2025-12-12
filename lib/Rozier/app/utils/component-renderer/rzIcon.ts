import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-icon'

export type RzIconData = RzElement & {
    class: string
}

export function rzIconRenderer(data: RzIconData) {
    const root = rzElement({
        tag: 'span',
        ...data,
        attributes: {
            ...data.attributes,
            class: data.class,
        },
    })

    return root
}
