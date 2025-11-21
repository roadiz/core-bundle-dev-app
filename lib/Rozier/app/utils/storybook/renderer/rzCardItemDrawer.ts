import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'
import { rzImageRenderer } from '~/utils/storybook/renderer/rzImage'
import { rzBadgeRenderer } from '~/utils/storybook/renderer/rzBadge'
import { type Image } from '~/utils/storybook/renderer/rzImage'
import type { Args as ButtonGroupArgs } from '../../../../stories/RzButtonGroup.stories'
import { type BadgeArgs } from '../../../../stories/RzBadge.stories'

export const COMPONENT_CLASS_NAME = 'rz-card-item-drawer'

export type Args = {
    overtitle?: string
    title?: string
    image?: Image
    badge?: BadgeArgs
    buttonGroup: ButtonGroupArgs
    buttonGroupTop?: ButtonGroupArgs
}

export function rzCardItemDrawerRenderer(
    args: Args,
    itemClass: string = COMPONENT_CLASS_NAME,
) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(itemClass)

    if (args.overtitle) {
        const overtitle = document.createElement('div')
        overtitle.classList.add(`${itemClass}__overtitle`)
        overtitle.textContent = args.overtitle
        wrapper.appendChild(overtitle)
    }

    if (args.title) {
        const title = document.createElement('div')
        title.classList.add(`${itemClass}__title`)
        title.textContent = args.title
        wrapper.appendChild(title)
    }

    if (args.image || args.badge) {
        const assetWrapper = document.createElement('div')
        assetWrapper.classList.add(`${itemClass}__asset`)
        wrapper.appendChild(assetWrapper)

        if (args.image) {
            const imageNode = rzImageRenderer(args.image)
            imageNode.classList.add(`${itemClass}__img`)
            assetWrapper.appendChild(imageNode)
        }

        if (args.badge) {
            const badgeNode = rzBadgeRenderer(args.badge)
            badgeNode.classList.add(`${itemClass}__badge`)
            assetWrapper.appendChild(badgeNode)
        }
    }

    if (args.buttonGroup) {
        const node = rzButtonGroupRenderer(args.buttonGroup)
        node.classList.add(`${itemClass}__action`)
        wrapper.appendChild(node)
    }

    if (args.buttonGroupTop) {
        const node = rzButtonGroupRenderer(args.buttonGroupTop)
        node.classList.add(`${itemClass}__action`, `${itemClass}__action--top`)
        wrapper.appendChild(node)
    }

    return wrapper
}
