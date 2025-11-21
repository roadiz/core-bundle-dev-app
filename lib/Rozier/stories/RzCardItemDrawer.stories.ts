import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type Args,
    rzCardItemDrawerRenderer,
} from '~/utils/storybook/renderer/rzCardItemDrawer'
// @ts-expect-error — image module declaration not recognized
import image from './assets/images/01.jpg'

/**
 * Layout is auto determined based on presence of `rz-drawer-item__title` `rz-drawer-item__overtitle` `rz-drawer-item__img` classes.
 */
const meta: Meta<Args> = {
    title: 'Components/Drawer/Card/Item',
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
        return rzCardItemDrawerRenderer(args)
    },
    args: {
        buttonGroupTop: undefined,
    },
}

export const WithoutImg: Story = {
    render: (args) => {
        return rzCardItemDrawerRenderer(args)
    },
    args: {
        buttonGroupTop: undefined,
        image: undefined,
        badge: undefined,
    },
}

export const PrivateDocument: Story = {
    render: (args) => {
        return rzCardItemDrawerRenderer(args)
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
        return rzCardItemDrawerRenderer(args)
    },
    args: {
        overtitle: undefined,
        title: undefined,
    },
}
