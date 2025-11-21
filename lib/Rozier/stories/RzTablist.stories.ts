import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as TabArgs } from './RzTablistItem.stories'
import {
    TABLIST_CLASS_NAME,
    rzTablistRenderer,
    rzTablistItemRenderer,
} from '../app/utils/storybook/renderer/rzTablist'

export type Args = {
    tabs: TabArgs[]
}

const meta: Meta<Args> = {
    title: 'Components/Tablist/root',
    tags: ['autodocs'],
    args: {
        tabs: Array(3)
            .fill(null)
            .map((_, i) => {
                return {
                    tag: 'button',
                    innerHTML: `Tab label ${i + 1}`,
                    selected: false,
                    attributes: {
                        id: `tab-${i + 1}`,
                    },
                    panel: {
                        id: `panel-${i + 1}`,
                    },
                }
            }),
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzTablistRenderer(args)
    },
}

export const WithSeparator: Story = {
    render: (args) => {
        const tablist = rzTablistRenderer(args)

        const separator = document.createElement('hr')
        separator.classList.add(`${TABLIST_CLASS_NAME}__separator`)
        tablist.appendChild(separator)

        const tab = rzTablistItemRenderer({
            tag: 'button',
            innerHTML: '<span class="rz-icon-ri--printer-line"></span>',
        })
        tablist.appendChild(tab)

        const tab2 = rzTablistItemRenderer({
            tag: 'button',
            innerHTML: '<span class="rz-icon-ri--file-list-3-line"></span>',
        })
        tablist.appendChild(tab2)

        return tablist
    },
}

export const WithTabPanels: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')

        const tablist = rzTablistRenderer(args)
        wrapper.appendChild(tablist)

        args.tabs.forEach((tabArgs) => {
            const tabPanel = document.createElement('div')
            tabPanel.classList.add('rz-tabpanel')
            tabPanel.setAttribute('role', 'tabpanel')
            tabPanel.textContent = `Content for ${tabArgs.attributes?.id}`

            if (tabArgs.attributes?.id)
                tabPanel.setAttribute('aria-labelledby', tabArgs.attributes?.id)
            if (tabArgs.panel?.id) tabPanel.id = tabArgs.panel.id
            if (tabArgs.panel?.hidden) tabPanel.setAttribute('hidden', 'true')

            wrapper.appendChild(tabPanel)
        })

        return wrapper
    },
}
