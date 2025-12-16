import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import { type Args as FormFieldArgs } from './RzFormField.stories'
import { useArgs } from 'storybook/preview-api'
import { rzButtonGroupRenderer } from '~/utils/component-renderer/rzButtonGroup'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

type ItemOptions = {
    itemIndex?: number
    totalItems?: number
    onDeleteClicked: () => void
    onAddClicked: () => void
}

export type Args = {
    schema: FormFieldArgs[]
    length: number
} & FormFieldArgs

const meta: Meta<Args> = {
    title: 'Components/Form/Collection',
    tags: ['autodocs'],
    args: {
        length: 0,
        label: 'Repeatable',
        iconClass: 'rz-icon-ri--repeat-2-line',
        description: '',
        help: 'Help text example',
        error: '',
        input: undefined,
        schema: [
            {
                label: 'Node title',
                input: {
                    type: 'text',
                    name: 'title-name',
                    id: 'title-id',
                },
            },
            {
                label: 'Input Field Label',
                badge: {
                    iconClass: 'rz-icon-ri--earth-line',
                },
                input: {
                    type: 'date',
                    name: 'datetime-name',
                    id: 'datetime-id',
                },
            },
            {
                label: 'Background color',
                badge: {
                    iconClass: 'rz-icon-ri--earth-line',
                },
                input: {
                    type: 'color',
                    name: 'background-color',
                    id: 'background-color-id',
                },
            },
            {
                label: 'Number of items',
                badge: {
                    iconClass: 'rz-icon-ri--earth-line',
                },
                input: {
                    type: 'number',
                    name: 'number-of-items',
                    id: 'number-of-items-id',
                },
            },
        ],
        buttonGroup: {
            size: 'md',
            buttons: [
                {
                    label: 'Add item',
                    iconClass: 'rz-icon-ri--add-line',
                    emphasis: 'secondary',
                },
            ],
        },
    },
}

export default meta
type Story = StoryObj<Args>

const COMPONENT_CLASS_NAME = 'rz-form-collection'

function headerRenderer(options: ItemOptions) {
    const header = document.createElement('div')
    header.classList.add(`${COMPONENT_CLASS_NAME}__item__header`)

    const icon = document.createElement('span')
    icon.classList.add('rz-icon-ri--earth-line')
    header.appendChild(icon)

    const label = document.createElement('span')
    label.classList.add(`${COMPONENT_CLASS_NAME}__item__title`)
    label.textContent = 'Repeatable'
    header.appendChild(label)

    const directionGroup = rzButtonGroupRenderer({
        size: 'sm',
        collapsed: true,
        buttons: [
            {
                iconClass: 'rz-icon-ri--arrow-up-line',
                attributes: {
                    'tooltip-text': 'Move up',
                    disabled: options.itemIndex === 0 ? 'true' : undefined,
                },
            },
            {
                iconClass: 'rz-icon-ri--arrow-down-line',
                attributes: {
                    'tooltip-text': 'Move down',
                    disabled:
                        options.totalItems !== undefined &&
                        (options.totalItems <= 1 ||
                            options.itemIndex === options.totalItems - 1)
                            ? 'true'
                            : undefined,
                },
            },
        ],
    })

    directionGroup.classList.add(`${COMPONENT_CLASS_NAME}__item--align-end`)
    header.appendChild(directionGroup)

    const removeButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--delete-bin-7-line',
        emphasis: 'tertiary',
        size: 'sm',
        color: 'danger',
        attributes: {
            'tooltip-text': 'Remove item',
        },
    })
    if (options?.onDeleteClicked) {
        removeButton.addEventListener('click', options.onDeleteClicked)
    }
    removeButton.classList.add(`${COMPONENT_CLASS_NAME}__remove-button`)
    header.appendChild(removeButton)

    return header
}

function insertZoneRenderer(options: ItemOptions & { tag?: string }) {
    const insertZone = document.createElement(options.tag || 'div')
    insertZone.classList.add(`${COMPONENT_CLASS_NAME}__insert-zone`)

    const hasItem = options?.totalItems !== undefined && options?.totalItems > 0
    const isBeforeItem = options?.itemIndex === options?.totalItems - 1

    const addButton = rzButtonRenderer({
        iconClass: 'rz-icon-ri--add-line',
        emphasis: 'secondary',
        size: 'sm',
        attributes: {
            is: 'rz-button',
            'tooltip-text': `Insert item ${hasItem ? (isBeforeItem ? 'after' : 'before') : ''}`,
        },
    })
    if (options?.onAddClicked) {
        addButton.addEventListener('click', options.onAddClicked)
    }
    insertZone.appendChild(addButton)
    return insertZone
}

function rzFormCollectionItemRenderer(
    items: Args['schema'],
    options: ItemOptions,
) {
    const wrapper = document.createElement('li')
    wrapper.classList.add(`${COMPONENT_CLASS_NAME}__item`)

    const header = headerRenderer(options)
    wrapper.appendChild(header)

    if (items.length) {
        const body = document.createElement('div')
        body.classList.add(`${COMPONENT_CLASS_NAME}__item__body`)
        wrapper.appendChild(body)

        for (const fieldArgs of items) {
            const field = rzFormFieldRenderer(fieldArgs)
            body.appendChild(field)
        }
    }

    const insertZoneEnd = insertZoneRenderer(options)
    wrapper.appendChild(insertZoneEnd)
    return wrapper
}

export const Default: Story = {
    render: (args) => {
        const [{ length }, updateArgs] = useArgs()

        function addItem() {
            updateArgs({ length: length + 1 })
        }
        function removeItem() {
            updateArgs({ length: length - 1 })
        }

        const list = document.createElement('ul')
        list.classList.add(`${COMPONENT_CLASS_NAME}__list`)

        const insertZone = insertZoneRenderer({
            tag: 'li',
            onAddClicked: addItem,
            onDeleteClicked: removeItem,
        })
        list.appendChild(insertZone)

        const field = rzFormFieldRenderer(args, list)
        const button = field.querySelector('.rz-button')
        button?.addEventListener('click', addItem)

        for (let i = 0; i < args.length; i++) {
            const item = rzFormCollectionItemRenderer(args.schema, {
                itemIndex: i,
                totalItems: args.length,
                onAddClicked: addItem,
                onDeleteClicked: removeItem,
            })
            list.appendChild(item)
        }

        return field
    },
}
