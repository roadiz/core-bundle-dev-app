import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as FormFieldArgs } from './RzFormField.stories'
import type { Args as FieldsetArgs } from './RzFieldset.stories'
import { rzFormFieldRenderer } from '../app/utils/storybook/renderer/rzFormField'
import { rzFieldsetRenderer } from '../app/utils/storybook/renderer/rzFieldset'

const COMPONENT_CLASS_NAME = 'rz-form'
const ORIENTATIONS = ['horizontal', 'vertical']

export type Args = {
    name: string
    fields: (FormFieldArgs | FieldsetArgs)[]
    orientation?: (typeof ORIENTATIONS)[number]
}

const meta: Meta<Args> = {
    title: 'Components/Form/Form',
    tags: ['autodocs'],
    args: {
        name: 'My form',
        orientation: 'vertical',
    },
    argTypes: {
        orientation: {
            control: 'select',
            options: ORIENTATIONS,
        },
    },
}

export default meta
type Story = StoryObj<Args>

function getCheckboxFieldList(args: Partial<FormFieldArgs>, length = 5) {
    return [...Array(length).keys()].map((i) => {
        const id = Math.random().toString(36).slice(2, 9)
        return {
            ...args,
            label: `Input Field Label ${i + 1}`,
            description: `This is the description for checkbox ${i + 1}.`,
            input: {
                ...(args.input || {}),
                name: args.input?.name || `fieldlist-name-${i + 1}-${id}`,
                type: args.input?.type || 'checkbox',
                id: id,
            },
        } as FormFieldArgs
    })
}

function fieldListRenderer(fields: Args['fields']) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(`${COMPONENT_CLASS_NAME}__field-list`)

    fields.forEach((fieldArgs) => {
        let node = null
        if ('formFieldsData' in fieldArgs) {
            node = rzFieldsetRenderer(fieldArgs)
        } else {
            node = rzFormFieldRenderer(fieldArgs as FormFieldArgs)
        }
        wrapper.appendChild(node)
    })

    return wrapper
}

function formRenderer(args: Args) {
    const form = document.createElement('form')
    const classList = [
        COMPONENT_CLASS_NAME,
        args.orientation === 'horizontal' &&
            `${COMPONENT_CLASS_NAME}__field-list--horizontal`,
    ].filter((c) => c) as string[]
    form.classList.add(...classList)

    const fieldList = fieldListRenderer(args.fields)
    form.appendChild(fieldList)

    const button = document.createElement('button')
    button.classList.add(`rz-button`)
    button.type = 'submit'
    button.textContent = 'Submit'
    form.appendChild(button)

    return form
}

export const Default: Story = {
    render: (args) => formRenderer(args),
    args: {
        fields: [
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
    },
}

export const WithFieldListHeader: Story = {
    render: (args) => {
        const form = formRenderer(args)

        const fieldList = fieldListRenderer([
            {
                label: 'Node title',
                required: true,
                input: {
                    name: 'title-name-WithFieldListHeader',
                    type: 'text',
                    id: 'title-id-WithFieldListHeader',
                },
            },
        ])
        fieldList.classList.add(
            `${COMPONENT_CLASS_NAME}__field-list--horizontal`,
        )
        form.insertBefore(fieldList, form.firstChild)

        return form
    },
    args: {
        fields: [
            {
                label: 'Input Field Label',
                badge: {
                    iconClass: 'rz-icon-ri--earth-line',
                },
                input: {
                    type: 'date',
                    name: 'datetime-name',
                    id: 'datetime-id-WithFieldListHeader',
                },
            },
        ],
    },
}

export const WithFieldListHeaderDual: Story = {
    render: (args) => {
        const form = formRenderer(args)

        const fieldList = fieldListRenderer([
            {
                label: 'Node title',
                required: true,
                input: {
                    name: 'title-name-WithFieldListHeaderDual',
                    type: 'text',
                    id: 'title-id-WithFieldListHeaderDual',
                },
            },
            {
                label: 'Published at',
                required: true,
                input: {
                    type: 'date',
                    name: 'published-at-WithFieldListHeaderDual',
                    id: 'published-at-id-WithFieldListHeaderDual',
                },
            },
        ])
        fieldList.classList.add(
            `${COMPONENT_CLASS_NAME}__field-list--horizontal`,
        )
        form.insertBefore(fieldList, form.firstChild)

        return form
    },
    args: {
        fields: [
            {
                label: 'Input Field Label',
                badge: {
                    iconClass: 'rz-icon-ri--earth-line',
                },
                input: {
                    type: 'date',
                    name: 'datetime-name-WithFieldListHeaderDual',
                    id: 'datetime-id-WithFieldListHeaderDual',
                },
            },
        ],
    },
}

export const SwitchList: Story = {
    render: (args) => {
        return formRenderer(args)
    },
    args: {
        fields: [
            {
                label: 'Simple text',
                input: {
                    type: 'text',
                    name: 'simple-text-SwitchList',
                    id: 'simple-text-SwitchList-id',
                },
            },
            ...getCheckboxFieldList({
                input: {
                    type: 'checkbox',
                    className: 'rz-switch',
                    name: 'simple-text-SwitchList-checkbox',
                    id: 'simple-text-SwitchList-checkbox-id',
                },
            }),
            {
                label: 'Simple text 2',
                input: {
                    type: 'text',
                    name: 'simple-text-2-SwitchList',
                    id: 'simple-text-2-SwitchList-id',
                },
            },
        ],
    },
}

export const SwitchListWithFieldset: Story = {
    render: (args) => {
        const form = formRenderer(args)

        const inlineFieldList = fieldListRenderer([
            {
                label: 'Node title',
                required: true,
                input: {
                    type: 'text',
                    name: 'title-name-WithFieldListHeaderDual',
                    id: 'title-id-WithFieldListHeaderDual',
                },
            },
            {
                label: 'Published at',
                required: true,
                input: {
                    type: 'date',
                    name: 'published-at-WithFieldListHeaderDual',
                    id: 'published-at-id-WithFieldListHeaderDual',
                },
            },
        ])
        inlineFieldList.classList.add(
            `${COMPONENT_CLASS_NAME}__field-list--horizontal`,
        )
        form.insertBefore(inlineFieldList, form.firstChild)

        return form
    },
    args: {
        fields: [
            {
                label: 'Simple text',
                input: {
                    type: 'text',
                    name: 'simple-text-SwitchList',
                    id: 'simple-text-SwitchList-id',
                },
            },
            {
                legend: 'Checkbox Fieldset',
                formFieldsData: getCheckboxFieldList({
                    input: {
                        type: 'checkbox',
                        className: 'rz-switch',
                        name: 'checkbox-SwitchList',
                        id: 'checkbox-SwitchList-id',
                    },
                }),
            },
            {
                legend: 'Radio Fieldset',
                formFieldsData: getCheckboxFieldList({
                    input: {
                        type: 'radio',
                        name: 'radio-SwitchList',
                        id: 'radio-SwitchList-id',
                    },
                }),
            },
            {
                label: 'Simple text 2',
                input: {
                    type: 'text',
                    name: 'simple-text-2-SwitchList',
                    id: 'simple-text-2-SwitchList-id',
                },
            },
        ],
    },
}
