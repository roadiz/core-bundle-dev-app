import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzCardData,
    rzCardRenderer,
} from '~/utils/component-renderer/rzCard'
// @ts-expect-error — image module declaration not recognized
import image from './assets/images/01.jpg'

type Args = RzCardData & {}

/**
 * Layout is auto determined based on presence of `rz-card__title` `rz-card__overtitle` `rz-card__img` classes.
 */
const meta: Meta<Args> = {
    title: 'Components/Card',
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
                    color: 'danger',
                    emphasis: 'tertiary',
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
        return rzCardRenderer(args)
    },
    args: {
        buttonGroupTop: undefined,
    },
}

export const WithoutImg: Story = {
    render: (args) => {
        return rzCardRenderer(args)
    },
    args: {
        buttonGroupTop: undefined,
        image: undefined,
        badge: undefined,
    },
}

export const PrivateDocument: Story = {
    render: (args) => {
        return rzCardRenderer(args)
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
        return rzCardRenderer(args)
    },
    args: {
        overtitle: undefined,
        title: undefined,
    },
}
