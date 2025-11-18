import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as FormFieldArgs } from './RzFormField.stories'
import type { Args as FieldsetArgs } from './RzFieldset.stories'
import { rzFormFieldRenderer } from '../app/utils/storybook/renderer/rzFormField'
import { rzFieldsetRenderer } from '../app/utils/storybook/renderer/rzFieldset'

const COMPONENT_CLASS_NAME = 'rz-form'

type FieldList = {
    horizontal?: boolean
    fields: (FormFieldArgs | FieldsetArgs)[]
}

export type Args = {
    fieldListGroup: FieldList[]
}

const meta: Meta<Args> = {
    title: 'Components/Form/Form',
    tags: ['autodocs'],
    args: {
        fieldListGroup: [
            {
                horizontal: false,
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
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function getCheckboxFieldList(args: Partial<FormFieldArgs>, length = 6) {
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

function fieldListRenderer(args: FieldList) {
    const wrapper = document.createElement('div')
    const className = `${COMPONENT_CLASS_NAME}__field-list`
    wrapper.classList.add(className)
    if (args.horizontal) {
        wrapper.classList.add(`${className}--horizontal`)
    }

    args.fields.forEach((fieldArgs) => {
        if ('formFieldsData' in fieldArgs) {
            const node = rzFieldsetRenderer(fieldArgs)
            wrapper.appendChild(node)
        } else {
            const node = rzFormFieldRenderer(fieldArgs as FormFieldArgs)
            wrapper.appendChild(node)
        }
    })

    return wrapper
}

function formRenderer(args: Args) {
    const form = document.createElement('form')
    form.classList.add(COMPONENT_CLASS_NAME)

    args.fieldListGroup.forEach((fieldList) => {
        const fieldListElement = fieldListRenderer(fieldList)
        form.appendChild(fieldListElement)
    })

    const button = document.createElement('button')
    button.classList.add(`rz-button`)
    button.type = 'submit'
    button.textContent = 'Submit'
    form.appendChild(button)

    return form
}

export const Default: Story = {
    render: (args) => formRenderer(args),
}

export const TwoHorizontalGroup: Story = {
    render: (args) => {
        return formRenderer(args)
    },
    args: {
        fieldListGroup: [
            {
                horizontal: true,
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
                        label: 'Simple text 2',
                        input: {
                            type: 'text',
                            name: 'simple-text-2-SwitchList',
                            id: 'simple-text-2-SwitchList-id',
                        },
                    },
                ],
            },
            {
                horizontal: true,
                fields: getCheckboxFieldList({
                    input: {
                        type: 'checkbox',
                        className: 'rz-switch',
                        name: 'simple-text-SwitchList-checkbox',
                        id: 'simple-text-SwitchList-checkbox-id',
                    },
                }),
            },
        ],
    },
}

export const WithFieldListHeaderDual: Story = {
    render: (args) => {
        return formRenderer(args)
    },
    args: {
        fieldListGroup: [
            {
                horizontal: true,
                fields: [
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
                ],
            },
            {
                horizontal: false,
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
        ],
    },
}

export const SwitchListWithFieldset: Story = {
    render: (args) => {
        return formRenderer(args)
    },
    args: {
        fieldListGroup: [
            {
                horizontal: true,
                fields: [
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
                ],
            },
            {
                horizontal: false,
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
                        horizontal: true,
                        formFieldsData: getCheckboxFieldList({
                            input: {
                                type: 'checkbox',
                                className: 'rz-switch',
                                name: 'checkbox-SwitchList',
                                id: 'checkbox-SwitchList-id',
                            },
                        }),
                    },
                ],
            },
            {
                horizontal: true,
                fields: getCheckboxFieldList({
                    horizontal: true,
                    input: {
                        type: 'radio',
                        name: 'radio-SwitchList',
                        id: 'radio-SwitchList-id',
                    },
                }),
            },
            {
                horizontal: false,
                fields: [
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
        ],
    },
}
