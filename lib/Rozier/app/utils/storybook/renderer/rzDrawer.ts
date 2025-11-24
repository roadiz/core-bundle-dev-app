import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import type { Args as FormFieldArgs } from '../../../../stories/RzFormField.stories'
import { type Args as DrawerItemArgs } from './rzDrawerItem'

export type RzDrawerArgs = FormFieldArgs & {
    layout: (typeof DRAWER_LAYOUTS)[number]
    items: DrawerItemArgs[]
}

export const COMPONENT_CLASS_NAME = 'rz-drawer'
export const DRAWER_LAYOUTS = ['grid', 'grid-larger', 'full']

export const defaultFormFieldData: FormFieldArgs = {
    label: 'Drawer label',
    iconClass: 'rz-icon-ri--image-line',
    description: 'Description example',
    badge: {
        label: '0/255',
        color: 'error' as const,
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
}

export function rzDrawerRenderer(args: RzDrawerArgs) {
    const wrapper = rzFormFieldRenderer(args)
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    if (args.layout) {
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--${args.layout}`)
    }

    const body = document.createElement('div')
    body.classList.add(`${COMPONENT_CLASS_NAME}__body`)
    wrapper.appendChild(body)

    return { wrapper, body }
}
