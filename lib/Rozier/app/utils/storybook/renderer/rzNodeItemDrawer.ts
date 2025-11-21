import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'
import { rzImageRenderer } from './rzImage'
import { type Image } from './rzImage'
import type { Args as ButtonGroupArgs } from './../../../../stories/RzButtonGroup.stories'

const COMPONENT_CLASS_NAME = 'rz-node-item-drawer'

export const MODIFIERS = [
    'home',
    'stack',
    'hidden',
    'unpublished',
    'archived',
    'locked',
    'datetime-publishable',
    'datetime-publishable-future',
]

export type Args = {
    modifiers?: typeof MODIFIERS
    image?: Image
    title: string
    buttonGroup: ButtonGroupArgs
}

export const defaultItemData: Args = {
    title: 'Node Item 1',
    buttonGroup: {
        size: 'sm',
        gap: 'sm',
        buttons: [
            {
                iconClass: 'rz-icon-ri--edit-line',
                attributes: { 'aria-label': 'Edit' },
                emphasis: 'tertiary',
            },
            {
                iconClass: 'rz-icon-ri--delete-bin-7-line',
                attributes: { 'aria-label': 'Delete' },
                emphasis: 'tertiary',
            },
        ],
    },
}

export function rzNodeItemDrawerRenderer(
    args: Args,
    itemClass: string = COMPONENT_CLASS_NAME,
) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(itemClass)
    if (args.modifiers) {
        args.modifiers.forEach((modifier) => {
            wrapper.classList.add(`${itemClass}--${modifier}`)
        })
    }

    const drag = document.createElement('span')
    drag.classList.add(`${itemClass}__drag`, 'rz-icon-ri--draggable')
    wrapper.appendChild(drag)

    if (args.title) {
        const title = document.createElement('div')
        title.classList.add(`${itemClass}__title`)
        title.textContent = args.title
        wrapper.appendChild(title)
    }

    if (args.image) {
        const assetWrapper = document.createElement('div')
        assetWrapper.classList.add(`${itemClass}__asset`)
        wrapper.appendChild(assetWrapper)

        if (args.image) {
            const imageNode = rzImageRenderer(args.image)
            imageNode.classList.add(`${itemClass}__img`)
            assetWrapper.appendChild(imageNode)
        }
    }

    if (args.buttonGroup) {
        const node = rzButtonGroupRenderer(args.buttonGroup)
        node.classList.add(`${itemClass}__action`)
        wrapper.appendChild(node)
    }

    return wrapper
}
