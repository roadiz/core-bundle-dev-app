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
    title: 'Components/Form',
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
            type: 'checkbox',
            label: `Input Field Label ${i + 1}`,
            description: `This is the description for checkbox ${i + 1}.`,
            name: `fieldlist-name-${i + 1}-${id}`,
            inputId: id,
            ...args,
        }
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
    form.classList.add(COMPONENT_CLASS_NAME)

    const fieldList = fieldListRenderer(args.fields)
    if (args.orientation === 'horizontal') {
        fieldList.classList.add(
            `${COMPONENT_CLASS_NAME}__field-list--horizontal`,
        )
    }
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
                type: 'text',
                name: 'title-name',
            },
            {
                label: 'Input Field Label',
                type: 'date',
                badgeIconClass: 'rz-icon-ri--earth-line',
                name: 'datetime-name',
            },
            {
                label: 'Background color',
                type: 'color',
                badgeIconClass: 'rz-icon-ri--earth-line',
                name: 'background-color',
            },
            {
                label: 'Number of items',
                type: 'number',
                badgeIconClass: 'rz-icon-ri--earth-line',
                name: 'number-of-items',
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
                type: 'text',
                name: 'title-name-WithFieldListHeader',
                required: true,
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
                type: 'date',
                badgeIconClass: 'rz-icon-ri--earth-line',
                name: 'datetime-name',
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
                type: 'text',
                name: 'title-name-WithFieldListHeaderDual',
                required: true,
            },
            {
                label: 'Published at',
                type: 'date',
                name: 'published-at-WithFieldListHeaderDual',
                required: true,
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
                type: 'date',
                badgeIconClass: 'rz-icon-ri--earth-line',
                name: 'datetime-name',
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
                type: 'text',
                name: 'simple-text-SwitchList',
            },
            ...getCheckboxFieldList({
                type: 'checkbox',
                inputClassName: 'rz-switch',
            }),
            {
                label: 'Simple text 2',
                type: 'text',
                name: 'simple-text-2-SwitchList',
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
                type: 'text',
                name: 'title-name-WithFieldListHeaderDual',
                required: true,
            },
            {
                label: 'Published at',
                type: 'date',
                name: 'published-at-WithFieldListHeaderDual',
                required: true,
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
                type: 'text',
                name: 'simple-text-SwitchList',
            },
            {
                legend: 'Checkbox Fieldset',
                formFieldsData: getCheckboxFieldList({
                    type: 'checkbox',
                    inputClassName: 'rz-switch',
                }),
            },
            {
                legend: 'Radio Fieldset',
                formFieldsData: getCheckboxFieldList({
                    type: 'radio',
                    name: 'radio-SwitchList',
                }),
            },
            {
                label: 'Simple text 2',
                type: 'text',
                name: 'simple-text-2-SwitchList',
            },
        ],
    },
}
