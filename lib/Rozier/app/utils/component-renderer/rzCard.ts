import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

import {
    type RzButtonGroupData,
    rzButtonGroupRenderer,
} from '~/utils/component-renderer/rzButtonGroup'
import {
    type RzImageData,
    rzImageRenderer,
} from '~/utils/component-renderer/rzImage'
import {
    type RzBadgeData,
    rzBadgeRenderer,
} from '~/utils/component-renderer/rzBadge'

export const COMPONENT_CLASS_NAME = 'rz-card'

export type RzCardData = RzElement & {
    overtitle?: string
    title?: string
    image?: RzImageData
    badge?: RzBadgeData
    buttonGroup: RzButtonGroupData
    buttonGroupTop?: RzButtonGroupData
}

export function rzCardRenderer(data: RzCardData) {
    const root = rzElement(data)
    root.classList.add(COMPONENT_CLASS_NAME)

    if (data.overtitle) {
        const overtitle = document.createElement('div')
        overtitle.classList.add(`${COMPONENT_CLASS_NAME}__overtitle`)
        overtitle.textContent = data.overtitle
        root.appendChild(overtitle)
    }

    if (data.title) {
        const title = document.createElement('div')
        title.classList.add(`${COMPONENT_CLASS_NAME}__title`)
        title.textContent = data.title
        root.appendChild(title)
    }

    if (data.image || data.badge) {
        const assetWrapper = document.createElement('div')
        assetWrapper.classList.add(`${COMPONENT_CLASS_NAME}__asset`)
        root.appendChild(assetWrapper)

        if (data.image) {
            const imageNode = rzImageRenderer(data.image)
            imageNode.classList.add(`${COMPONENT_CLASS_NAME}__img`)
            assetWrapper.appendChild(imageNode)
        }

        if (data.badge) {
            const badgeNode = rzBadgeRenderer(data.badge)
            badgeNode.classList.add(`${COMPONENT_CLASS_NAME}__badge`)
            assetWrapper.appendChild(badgeNode)
        }
    }

    if (data.buttonGroup) {
        const node = rzButtonGroupRenderer(data.buttonGroup)
        node.classList.add(`${COMPONENT_CLASS_NAME}__action`)
        root.appendChild(node)
    }

    if (data.buttonGroupTop) {
        const node = rzButtonGroupRenderer(data.buttonGroupTop)
        node.classList.add(
            `${COMPONENT_CLASS_NAME}__action`,
            `${COMPONENT_CLASS_NAME}__action--top`,
        )
        root.appendChild(node)
    }

    return root
}
