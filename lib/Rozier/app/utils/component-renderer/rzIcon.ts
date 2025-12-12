import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-icon'

export type rzBadgeData = RzElement & {
    class: string
}

export function rzIconRenderer(data: rzBadgeData) {
    const root = rzElement({
        ...data,
        attributes: {
            ...data.attributes,
            class: data.class,
        },
    })

    return root
}
