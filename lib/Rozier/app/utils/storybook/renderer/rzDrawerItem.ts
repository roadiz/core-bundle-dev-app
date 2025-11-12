import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'
import { rzImageRenderer } from '~/utils/storybook/renderer/rzImage'
import type { Args } from '../../../../stories/rzDrawerItem.stories'

const COMPONENT_CLASS_NAME = 'rz-drawer-item'

export function rzDrawerItemRenderer(args: Args) {
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

    if (args.image) {
        const imageNode = rzImageRenderer(args.image)
        imageNode.classList.add(`${COMPONENT_CLASS_NAME}__img`)
        wrapper.appendChild(imageNode)
    }

    if (args.buttonGroup) {
        const node = rzButtonGroupRenderer(args.buttonGroup)
        node.classList.add(`${COMPONENT_CLASS_NAME}__button-group`)
        wrapper.appendChild(node)
    }

    if (args.buttonGroupTop) {
        const node = rzButtonGroupRenderer(args.buttonGroupTop)
        node.classList.add(
            `${COMPONENT_CLASS_NAME}__button-group`,
            `${COMPONENT_CLASS_NAME}__button-group--top`,
        )
        wrapper.appendChild(node)
    }

    return wrapper
}
