import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

import {
    type RzButtonGroupOptions,
    rzButtonGroupRenderer,
} from '~/utils/component-renderer/rzButtonGroup'
import {
    type RzImageOptions,
    rzImageRenderer,
} from '~/utils/component-renderer/rzImage'
import {
    type RzBadgeOptions,
    rzBadgeRenderer,
} from '~/utils/component-renderer/rzBadge'

export const COMPONENT_CLASS_NAME = 'rz-card'

export type RzCardOptions = RzElement & {
    overtitle?: string
    title?: string
    image?: RzImageOptions
    badge?: RzBadgeOptions
    buttonGroup?: RzButtonGroupOptions
    buttonGroupTop?: RzButtonGroupOptions
}

export function rzCardRenderer(options: RzCardOptions) {
    const root = rzElement(options)
    root.classList.add(COMPONENT_CLASS_NAME)

    if (options.overtitle) {
        const overtitle = document.createElement('div')
        overtitle.classList.add(`${COMPONENT_CLASS_NAME}__overtitle`)
        overtitle.textContent = options.overtitle
        root.appendChild(overtitle)
    }

    if (options.title) {
        const title = document.createElement('div')
        title.classList.add(`${COMPONENT_CLASS_NAME}__title`)
        title.textContent = options.title
        root.appendChild(title)
    }

    if (options.image || options.badge) {
        const assetWrapper = document.createElement('div')
        assetWrapper.classList.add(`${COMPONENT_CLASS_NAME}__asset`)
        root.appendChild(assetWrapper)

        if (options.image) {
            const imageNode = rzImageRenderer(options.image)
            imageNode.classList.add(`${COMPONENT_CLASS_NAME}__img`)
            assetWrapper.appendChild(imageNode)
        }

        if (options.badge) {
            const badgeNode = rzBadgeRenderer(options.badge)
            badgeNode.classList.add(`${COMPONENT_CLASS_NAME}__badge`)
            assetWrapper.appendChild(badgeNode)
        }
    }

    if (options.buttonGroup) {
        const node = rzButtonGroupRenderer(options.buttonGroup)
        node.classList.add(`${COMPONENT_CLASS_NAME}__action`)
        root.appendChild(node)
    }

    if (options.buttonGroupTop) {
        const node = rzButtonGroupRenderer(options.buttonGroupTop)
        node.classList.add(
            `${COMPONENT_CLASS_NAME}__action`,
            `${COMPONENT_CLASS_NAME}__action--top`,
        )
        root.appendChild(node)
    }

    return root
}
