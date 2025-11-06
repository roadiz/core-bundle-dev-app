import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'
import { rzBadgeRenderer } from '../app/utils/storybook/renderer/rzBadge'
import type { ButtonArgs } from './rzButton.stories'
import type { BadgeArgs } from './rzBadge.stories'

const COMPONENT_CLASS_NAME = 'rz-form-field-wrapper'

export type Args = {
    label: string
    iconClass?: string
    badge?: BadgeArgs
    buttons?: ButtonArgs[]
}

const meta: Meta<Args> = {
    title: 'Components/Form/FieldWrapper',
    tags: ['autodocs'],
    args: {
        label: 'Field name',
        iconClass: 'rz-icon-ri--image-line',
        badge: {
            label: '10/255',
            color: 'error',
            size: 'xs',
        },
        buttons: [
            {
                label: 'Upload',
                iconClass: 'rz-icon-ri--upload-line',
            },
            {
                label: 'Explore',
                iconClass: 'rz-icon-ri--add-line',
            },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function rzFormFieldWrapperHeaderRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(`${COMPONENT_CLASS_NAME}__header`)

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(
            `${COMPONENT_CLASS_NAME}__header__icon`,
            args.iconClass,
        )
        wrapper.appendChild(icon)
    }

    if (args.label) {
        const label = document.createElement('div')
        label.classList.add(`${COMPONENT_CLASS_NAME}__header__label`)
        label.textContent = args.label
        wrapper.appendChild(label)
    }

    if (args.badge) {
        const badgeElement = rzBadgeRenderer(args.badge)
        wrapper.appendChild(badgeElement)
    }

    if (args.buttons?.length) {
        args.buttons.forEach((buttonArgs) => {
            const buttonElement = rzButtonRenderer({
                ...buttonArgs,
                emphasis: 'medium',
                size: 'sm',
            })
            buttonElement.classList.add(
                `${COMPONENT_CLASS_NAME}__header__button`,
            )
            wrapper.appendChild(buttonElement)
        })
    }

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.classList.add(COMPONENT_CLASS_NAME)

        const header = rzFormFieldWrapperHeaderRenderer(args)
        wrapper.appendChild(header)

        const body = document.createElement('div')
        body.classList.add(`${COMPONENT_CLASS_NAME}__body`)
        wrapper.appendChild(body)

        return wrapper
    },
}
