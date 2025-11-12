import type { Meta, StoryObj } from '@storybook/html-vite'
import image from './assets/images/01.jpg'
import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'
import { rzImageRenderer, type Image } from '~/utils/storybook/renderer/rzImage'
import type { Args as ButtonGroupArgs } from './RzButtonGroup.stories'

const COMPONENT_CLASS_NAME = 'rz-drawer-item'

type Args = {
    overtitle?: string
    title?: string
    image?: Image
    buttonGroup: ButtonGroupArgs
    buttonGroupTop?: ButtonGroupArgs
}

/**
 * Layout is auto determined based on presence of `rz-drawer-item__title` `rz-drawer-item__overtitle` `rz-drawer-item__img` classes.
 */
const meta: Meta<Args> = {
    title: 'Components/Drawer/Item',
    tags: ['autodocs'],
    args: {
        overtitle: 'Overtitle example',
        title: 'Title example',
        image: {
            src: image,
            width: 110,
            height: 94,
        },
        buttonGroup: {
            gap: 'sm',
            size: 'sm',
            buttons: [
                {
                    iconClass: 'rz-icon-ri--equalizer-3-line',
                    emphasis: 'primary',
                },
                {
                    iconClass: 'rz-icon-ri--delete-bin-7-line',
                    color: 'error-light',
                },
            ],
        },
        buttonGroupTop: {
            gap: 'sm',
            size: 'sm',
            buttons: [
                {
                    iconClass: 'rz-icon-ri--zoom-in-line',
                    emphasis: 'primary',
                },
            ],
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function rzDrawerItemRenderer(args: Args) {
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

export const Default: Story = {
    render: (args) => {
        return rzDrawerItemRenderer(args)
    },
    args: {
        buttonGroupTop: undefined,
    },
}

export const WithoutImg: Story = {
    render: (args) => {
        return rzDrawerItemRenderer(args)
    },
    args: {
        buttonGroupTop: undefined,
        image: undefined,
    },
}

export const OnlyImg: Story = {
    render: (args) => {
        return rzDrawerItemRenderer(args)
    },
    args: {
        overtitle: undefined,
        title: undefined,
    },
}
