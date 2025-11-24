import type { Meta, StoryObj } from '@storybook/html-vite'
import { defaultFormFieldData } from '~/utils/storybook/renderer/rzDrawer'
import {
    type Args as ItemArgs,
    defaultItemData,
    rzChildrenNodesWidgetItemRenderer,
} from '~/utils/storybook/renderer/rzChildrenNodesWidgetItem'
import type { Args as FormFieldArgs } from './RzFormField.stories'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'

export type Args = FormFieldArgs & {
    items: ItemArgs[]
}

const COMPONENT_CLASS_NAME = 'rz-children-nodes-widget'

const meta: Meta<Args> = {
    title: 'Components/Form/ChildrenNodesWidget/Root',
    tags: ['autodocs'],
    args: {
        ...defaultFormFieldData,
        badge: undefined,
        description: 'Manage the blocs of the page here.',
        label: 'Blocs',
        iconClass: 'rz-icon-ri--layout-4-line',
        buttonGroup: {
            size: 'md',
            gap: 'md',
            buttons: [
                {
                    label: 'Add bloc',
                    iconClass: 'rz-icon-ri--add-line',
                },
            ],
        },
        items: [defaultItemData, defaultItemData, defaultItemData],
    },
}

export default meta
type Story = StoryObj<Args>

export function rzChildrenNodesWidgetRenderer(args: Args) {
    const wrapper = rzFormFieldRenderer(args)
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    const body = document.createElement('div')
    body.classList.add(`${COMPONENT_CLASS_NAME}__body`)
    wrapper.appendChild(body)

    args.items.forEach((itemArgs) => {
        const itemNode = rzChildrenNodesWidgetItemRenderer(itemArgs)
        body.appendChild(itemNode)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzChildrenNodesWidgetRenderer(args)
    },
}
