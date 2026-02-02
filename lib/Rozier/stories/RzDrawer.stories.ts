import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import { type Args as FormFieldArgs } from './RzFormField.stories'
import {
    type RzCardOptions,
    rzCardRenderer,
} from '~/utils/component-renderer/rzCard'
// @ts-expect-error — image module declaration not recognized
import imageHorizontal from './assets/images/01.jpg'
// @ts-expect-error — image module declaration not recognized
import imageVertical from './assets/images/02.jpg'

const COMPONENT_CLASS_NAME = 'rz-drawer'
const DRAWER_LAYOUTS = ['grid', 'grid-larger', 'full']

export type Args = FormFieldArgs & {
    layout: (typeof DRAWER_LAYOUTS)[number]
    items: RzCardOptions[]
}

const NODE_WITH_IMG_ITEM: RzCardOptions = {
    overtitle: 'Overtitle example',
    title: 'Title example',
    image: {
        src: imageVertical,
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
                emphasis: 'tertiary',
                color: 'danger',
            },
        ],
    },
}

const NODE_ITEM: RzCardOptions = {
    overtitle: 'Overtitle example',
    title: 'Title example',
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
                emphasis: 'tertiary',
                color: 'danger',
            },
        ],
    },
}

const DOCUMENT_ITEM: RzCardOptions = {
    image: {
        src: imageHorizontal,
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
                emphasis: 'tertiary',
                color: 'danger',
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
}

const meta: Meta<Args> = {
    title: 'Components/Form/Drawer/Root',
    tags: ['autodocs'],
    args: {
        label: 'Drawer label',
        iconClass: 'rz-icon-ri--image-line',
        description: 'Description example',
        help: 'Help text example',
        error: 'Error message example',
        badge: {
            label: '0/255',
            color: 'danger' as const,
            size: 'xs' as const,
        },
        buttonGroup: {
            size: 'md' as const,
            gap: 'md' as const,
            buttons: [
                {
                    label: 'Upload',
                    iconClass: 'rz-icon-ri--upload-line',
                    size: 'sm' as const,
                },
                {
                    label: 'Explore',
                    iconClass: 'rz-icon-ri--add-line',
                    size: 'sm' as const,
                },
            ],
        },
        input: undefined,
        items: [...Array(10).fill(null)],
        layout: 'grid',
    },
    argTypes: {
        layout: {
            control: { type: 'select' },
            options: DRAWER_LAYOUTS,
        },
    },
}

export default meta
type Story = StoryObj<Args>

function rzDrawerRenderer(args: Args) {
    const body = document.createElement('ul')
    body.classList.add(`${COMPONENT_CLASS_NAME}__body`)

    args.items.forEach((itemArgs) => {
        if (itemArgs === null) {
            const item = document.createElement('li')
            item.style = `
                width: 100%;
                height: 100px;
                background-color: #e0e0e0;
                border: 1px solid #ccc;
                border-radius: 4px;
            `
            body.appendChild(item)
        } else {
            const itemNode = rzCardRenderer({ ...itemArgs, tag: 'li' })
            body.appendChild(itemNode)
        }
    })

    const wrapper = rzFormFieldRenderer(args, body)
    wrapper.classList.add(COMPONENT_CLASS_NAME)
    if (args.layout) {
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--${args.layout}`)
    }

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
}

export const NodeEntityDrawer: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [...Array(4).fill(null)].map(() => NODE_WITH_IMG_ITEM),
        layout: 'grid',
    },
}

export const NodeEntityWithoutImgDrawer: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [...Array(10).fill(null)].map(() => NODE_ITEM),
        layout: 'grid',
    },
}

export const NodeEntityMixed: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [
            NODE_ITEM,
            NODE_WITH_IMG_ITEM,
            NODE_WITH_IMG_ITEM,
            NODE_ITEM,
            NODE_ITEM,
            NODE_ITEM,
            NODE_WITH_IMG_ITEM,
            NODE_ITEM,
            NODE_WITH_IMG_ITEM,
        ],
        layout: 'grid',
    },
}

export const DocumentDrawer: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        items: [...Array(10).fill(null)].map(() => DOCUMENT_ITEM),
        layout: 'grid-larger',
    },
}

export const DocumentWithPictureTemplateDrawer: Story = {
    render: (args) => {
        return rzDrawerRenderer(args)
    },
    args: {
        layout: 'grid-larger',
        items: [
            {
                ...DOCUMENT_ITEM,
                image: {
                    src: imageHorizontal,
                    width: 110,
                    height: 94,
                    sources: [
                        {
                            type: 'image/webp',
                            srcset: imageVertical,
                        },
                    ],
                },
            },
            {
                ...DOCUMENT_ITEM,
                image: {
                    src: imageVertical,
                    width: 110,
                    height: 94,
                    sources: [
                        {
                            type: 'image/webp',
                            srcset: imageHorizontal,
                        },
                    ],
                },
            },
            DOCUMENT_ITEM,
            DOCUMENT_ITEM,
        ],
    },
}
