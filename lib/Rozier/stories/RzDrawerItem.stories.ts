import type { Meta, StoryObj } from '@storybook/html-vite'
import { type Image } from '~/utils/storybook/renderer/rzImage'
import type { Args as ButtonGroupArgs } from './RzButtonGroup.stories'
import { rzDrawerItemRenderer } from '~/utils/storybook/renderer/rzDrawerItem'
import { type BadgeArgs } from './RzBadge.stories'
// @ts-expect-error — image module declaration not recognized
import image from './assets/images/01.jpg'

export type Args = {
    overtitle?: string
    title?: string
    image?: Image
    badge?: BadgeArgs
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
        badge: undefined,
    },
}

export const PrivateDocument: Story = {
    render: (args) => {
        return rzDrawerItemRenderer(args)
    },
    args: {
        overtitle: undefined,
        title: undefined,
        image: undefined,
        badge: {
            iconClass: 'rz-icon-ri--lock-2-line',
            size: 'md',
        },
        buttonGroupTop: undefined,
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
