import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'
import { rzElement } from '~/utils/component-renderer/rzElement'
import { rzIconRenderer } from '~/utils/component-renderer/rzIcon'

export type Args = {
    legend: string
}

const meta: Meta<Args> = {
    title: 'Components/ThemeSwitcher',
    tags: ['autodocs'],
    args: {
        legend: 'Choose the app color scheme',
    },
}

export default meta
type Story = StoryObj<Args>

function radioRenderer(options: {
    label: string
    icon: string
    value: string
    checked?: boolean
}) {
    const label = document.createElement('label')
    label.classList.add('rz-theme-fieldset__item')
    label.textContent = options.label

    const input = document.createElement('input')
    input.type = 'radio'
    input.name = 'theme'
    input.value = options.value
    if (options.checked) {
        input.checked = true
    }

    const icon = rzIconRenderer({ class: options.icon })

    label.appendChild(input)
    label.appendChild(icon)
    return label
}

const radioElements = [
    {
        label: 'System',
        icon: 'rz-icon-ri--computer-line',
        value: 'light dark',
        checked: true,
    },
    { label: 'Light', icon: 'rz-icon-ri--sun-line', value: 'light' },
    { label: 'Dark', icon: 'rz-icon-ri--moon-line', value: 'dark' },
].map((options) => {
    return radioRenderer(options)
})

function rzThemeSwitcherRenderer(args: Args) {
    const target = rzButtonRenderer({
        is: 'rz-button',
        iconClass: 'rz-icon-ri--color-filter-line',
        attributes: {
            'aria-label': 'Ouvrir le sélecteur de thème',
        },
    })

    const popoverElement = rzElement({
        tag: 'fieldset',
        is: 'rz-theme-fieldset',
        innerHTML: `
            <legend class="rz-visually-hidden">${args.legend}</legend>
                ${radioElements
                    .map((el) => {
                        return el.outerHTML
                    })
                    .join('')}
        `,
    })

    // const popoverElement = rzElement({
    //     tag: 'fieldset',
    //     is: 'rz-theme-fieldset',
    //     attributes: {
    //         class: 'rz-dropdown',
    //     },
    //     innerHTML: `
    //         <div class="rz-dropdown__head">
    //             <div class="rz-dropdown__title">Select a theme</div>
    //         </div>
    //         <legend class="rz-visually-hidden">${args.legend}</legend>
    //         <div class="rz-dropdown__list">
    //             ${radioElements
    //                 .map((el) => {
    //                     el.classList.add('rz-dropdown__item')
    //                     return el.outerHTML
    //                 })
    //                 .join('')}
    //         </div>
    //     `,
    // })

    const { popover } = rzPopoverRenderer({
        targetElement: { element: target },
        offset: 12,
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
