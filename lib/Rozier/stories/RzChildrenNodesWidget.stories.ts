import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type Args as ItemArgs,
    defaultItemData,
    rzChildrenNodesWidgetItemRenderer,
} from '~/utils/storybook/renderer/rzChildrenNodesWidgetItem'
import type { Args as FormFieldArgs } from './RzFormField.stories'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'

export type Args = FormFieldArgs & {
    items: (ItemArgs | ItemArgs[])[]
}

const COMPONENT_CLASS_NAME = 'rz-children-nodes-widget'

const meta: Meta<Args> = {
    title: 'Components/Form/ChildrenNodesWidget/Root',
    tags: ['autodocs'],
    args: {
        input: undefined,
        badge: undefined,
        description: 'Manage the blocs of the page here.',
        help: 'this is a help text for the children nodes widget.',
        error: 'This is an error message for the children nodes widget.',
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
        items: [
            defaultItemData,
            [defaultItemData, defaultItemData],
            defaultItemData,
            defaultItemData,
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function listRenderer(items: Args['items']) {
    const list = document.createElement('ul')
    list.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    items.forEach((itemArgs) => {
        if (Array.isArray(itemArgs)) {
            const item = document.createElement('li')
            list.appendChild(item)

            const subList = listRenderer(itemArgs)
            item.appendChild(subList)
        } else {
            const itemNode = rzChildrenNodesWidgetItemRenderer({
                ...itemArgs,
                tag: 'li',
            })
            list.appendChild(itemNode)
        }
    })

    return list
}

function rzChildrenNodesWidgetRenderer(args: Args) {
    const body = document.createElement('div')
    body.classList.add(`${COMPONENT_CLASS_NAME}__body`)
    const list = listRenderer(args.items)
    body.appendChild(list)

    const wrapper = rzFormFieldRenderer(args, body)
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzChildrenNodesWidgetRenderer(args)
    },
}
