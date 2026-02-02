import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'

export type Args = {
    legend: string
}

const meta: Meta<Args> = {
    title: 'Components/ColorSchemeSwitcher',
    tags: ['autodocs'],
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: () => {
        const id = 'color-scheme-switcher-storybook'
        const content = document.createElement('rz-color-scheme-switcher')
        content.innerHTML = `
<menu class="rz-dropdown__list">
    <li class="">
                    <button class="rz-dropdown__item" type="button" data-value="system" command="--update-color-scheme"
                    commandfor="${id}"
                    >
                        <div class="rz-dropdown__item__text-wrapper">
                                    <span class="rz-dropdown__item__label">color.system</span>
                                            </div>
                                        <span class="rz-dropdown__item__icon rz-icon-ri--computer-line"></span>
                                </button>
    </li>



    <li class="">
                    <button class="rz-dropdown__item rz-dropdown__item--selected" type="button" data-value="light" command="--update-color-scheme" commandfor="${id}">
                        <div class="rz-dropdown__item__text-wrapper">
                                    <span class="rz-dropdown__item__label">color.light</span>
                                            </div>
                                        <span class="rz-dropdown__item__icon rz-icon-ri--sun-line"></span>
                                </button>
    </li>



    <li class="">
                    <button class="rz-dropdown__item" type="button" data-value="dark" command="--update-color-scheme" commandfor="${id}">
                        <div class="rz-dropdown__item__text-wrapper">
                                    <span class="rz-dropdown__item__label">color.dark</span>
                                            </div>
                                        <span class="rz-dropdown__item__icon rz-icon-ri--moon-line"></span>
                                </button>
    </li>

			        </menu>
        `

        const button = document.createElement('button')
        button.textContent = 'Switch Theme'

        const { popover } = rzPopoverRenderer({
            popoverElement: {
                tag: 'rz-color-scheme-switcher',
                id: id,
                element: content,
            },
            targetElement: { element: button },
        })

        return popover
    },
}
