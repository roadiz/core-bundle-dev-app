import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'
import { rzImageRenderer } from '~/utils/storybook/renderer/rzImage'
import { rzBadgeRenderer } from '~/utils/storybook/renderer/rzBadge'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import type { Args as RzDrawerItemArgs } from '../../../../stories/RzDrawerItem.stories'
import type { Args as RzDrawerArgs } from '../../../../stories/RzDrawer.stories'

const COMPONENT_CLASS_NAME = 'rz-drawer'

export function rzDrawerItemRenderer(
    args: RzDrawerItemArgs,
    COMPONENT_CLASS_NAME = 'rz-drawer__item',
) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    if (args.overtitle) {
        const overtitle = document.createElement('div')
        overtitle.classList.add(`${COMPONENT_CLASS_NAME}__overtitle`)
        overtitle.textContent = args.overtitle
        wrapper.appendChild(overtitle)
    }

    if (args.title) {
        const title = document.createElement('div')
        title.classList.add(`${COMPONENT_CLASS_NAME}__title`)
        title.textContent = args.title
        wrapper.appendChild(title)
    }

    if (args.image || args.badge) {
        const assetWrapper = document.createElement('div')
        assetWrapper.classList.add(`${COMPONENT_CLASS_NAME}__asset`)
        wrapper.appendChild(assetWrapper)

        if (args.image) {
            const imageNode = rzImageRenderer(args.image)
            imageNode.classList.add(`${COMPONENT_CLASS_NAME}__img`)
            assetWrapper.appendChild(imageNode)
        }

        if (args.badge) {
            const badgeNode = rzBadgeRenderer(args.badge)
            badgeNode.classList.add(`${COMPONENT_CLASS_NAME}__badge`)
            assetWrapper.appendChild(badgeNode)
        }
    }

    if (args.buttonGroup) {
        const node = rzButtonGroupRenderer(args.buttonGroup)
        node.classList.add(`${COMPONENT_CLASS_NAME}__action`)
        wrapper.appendChild(node)
    }

    if (args.buttonGroupTop) {
        const node = rzButtonGroupRenderer(args.buttonGroupTop)
        node.classList.add(
            `${COMPONENT_CLASS_NAME}__action`,
            `${COMPONENT_CLASS_NAME}__action--top`,
        )
        wrapper.appendChild(node)
    }

    return wrapper
}

export function rzDrawerRenderer(args: RzDrawerArgs) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)
    if (args.moreColumns)
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--more-columns`)

    const head = rzFormFieldRenderer(args.formField)
    head.classList.add(`${COMPONENT_CLASS_NAME}__head`)
    wrapper.appendChild(head)

    const body = document.createElement('div')
    body.classList.add(`${COMPONENT_CLASS_NAME}__body`)
    wrapper.appendChild(body)

    args.items.forEach((itemArgs) => {
        const itemNode = rzDrawerItemRenderer(itemArgs)
        itemNode.classList.add(`${COMPONENT_CLASS_NAME}__item`)
        body.appendChild(itemNode)
    })

    return wrapper
}
