import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'
import { rzElement } from '~/utils/component-renderer/rzElement'

export type Args = {
    legend: string
}

const meta: Meta<Args> = {
    title: 'Components/ThemeSwitcher',
    tags: ['autodocs'],
    args: {
        legend: 'Choose an app color scheme',
    },
}

export default meta
type Story = StoryObj<Args>

function radioRenderer(options: {
    label: string
    value: string
    checked?: boolean
}) {
    const wrapper = document.createElement('div')
    const label = document.createElement('label')
    label.textContent = options.label
    const input = document.createElement('input')
    input.type = 'radio'
    input.name = 'theme'
    input.value = options.value
    if (options.checked) {
        input.checked = true
    }
    label.appendChild(input)
    wrapper.appendChild(label)
    return wrapper
}

function rzThemeSwitcherRenderer(args: Args) {
    const target = rzButtonRenderer({
        label: 'Appeareance',
        iconClass: 'rz-icon--theme-switcher',
    })

    const popoverElement = rzElement({
        tag: 'fieldset',
        is: 'rz-theme-fieldset',
        innerHTML: `<legend>${args.legend}</legend>`,
    })

    ;[
        { label: 'System', value: 'light dark', checked: true },
        { label: 'Light', value: 'light' },
        { label: 'Dark', value: 'dark' },
    ].forEach((options) => {
        popoverElement.appendChild(radioRenderer(options))
    })

    const { popover } = rzPopoverRenderer({
        targetElement: { element: target },
        placement: 'bottom-start',
        popoverElement: {
            id: 'theme-switcher-popover',
            element: popoverElement,
        },
    })

    return popover
}

export const Default: Story = {
    render: (args) => {
        return rzThemeSwitcherRenderer(args)
    },
}
