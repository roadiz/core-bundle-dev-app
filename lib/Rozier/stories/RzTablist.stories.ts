import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as TabArgs } from './RzTab.stories'
import { rzTabRenderer } from '../app/utils/storybook/renderer/rzTab'

const COMPONENT_CLASS_NAME = 'rz-tablist'

type Args = {
    tabs: TabArgs[]
}

function getTab(id: number, args: Partial<TabArgs> = {}) {
    return {
        tag: 'button',
        innerHTML: `Tab label ${id}`,
        selected: false,
        variant: 'filled',
        ...args,
        attributes: {
            id: args.attributes?.id || `tab-${id}`,
        },
        panel: {
            id: args.panel?.id || `panel-${id}`,
        },
    }
}

const meta: Meta<Args> = {
    title: 'Components/Tab/Tablist',
    tags: ['autodocs'],
    args: {
        variant: 'filled',
        tabs: [getTab(1, { selected: true }), getTab(2), getTab(3)],
    },
}

export default meta
type Story = StoryObj<Args>

function tabPanelRenderer(args: Tab) {
    const tabPanel = document.createElement('div')
    tabPanel.classList.add('rz-tabpanel')
    tabPanel.setAttribute('role', 'tabpanel')
    tabPanel.setAttribute('aria-labelledby', args.attributes?.id)
    tabPanel.textContent = `Content for ${args.attributes?.id}`
    tabPanel.id = args.panel.id

    if (args.panel.hidden) {
        tabPanel.setAttribute('hidden', 'true')
    }

    return tabPanel
}

function rzTablistRenderer(args: Args) {
    const wrapper = document.createElement(COMPONENT_CLASS_NAME)
    const classList = [
        COMPONENT_CLASS_NAME,
        args.variant && `${COMPONENT_CLASS_NAME}--${args.variant}`,
    ].filter((c) => c) as string[]
    wrapper.classList.add(...classList)

    const tablist = document.createElement('div')
    tablist.classList.add(`${COMPONENT_CLASS_NAME}__inner`)
    tablist.setAttribute('role', 'tablist')
    wrapper.appendChild(tablist)

    args.tabs.forEach((tabArgs) => {
        const tab = rzTabRenderer(tabArgs)
        tab.classList.add(`${COMPONENT_CLASS_NAME}__tab`)
        tab.setAttribute('role', 'tab')
        tab.setAttribute('type', 'button')
        if (tabArgs.panel?.id)
            tab.setAttribute('aria-controls', tabArgs.panel?.id)
        tab.setAttribute(
            'aria-selected',
            (tabArgs.selected || false).toString(),
        )

        tablist.appendChild(tab)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzTablistRenderer(args)
    },
}

export const Underline: Story = {
    render: (args) => {
        return rzTablistRenderer(args)
    },
    args: {
        variant: 'underlined',
    },
}

function rzTablistWithSeparatorRenderer(args: Args) {
    const tablist = rzTablistRenderer(args)

    const inner = tablist.querySelector(`.${COMPONENT_CLASS_NAME}__inner`)

    const separator = document.createElement('hr')
    separator.classList.add(`${COMPONENT_CLASS_NAME}__separator`)
    inner?.appendChild(separator)

    const tab = rzTabRenderer({
        tag: 'button',
        innerHTML: '<span class="rz-icon-ri--printer-line"></span>',
    })
    inner?.appendChild(tab)

    const tab2 = rzTabRenderer({
        tag: 'button',
        innerHTML: '<span class="rz-icon-ri--file-list-3-line"></span>',
    })
    inner?.appendChild(tab2)

    return tablist
}

export const WithSeparator: Story = {
    render: (args) => {
        return rzTablistWithSeparatorRenderer(args)
    },
}

export const UnderlinedWithSeparator: Story = {
    render: (args) => {
        return rzTablistWithSeparatorRenderer(args)
    },
    args: {
        variant: 'underlined',
    },
}

export const WithTabPanels: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')

        const tablist = rzTablistRenderer(args)
        wrapper.appendChild(tablist)

        args.tabs.forEach((tabArgs) => {
            const panel = tabPanelRenderer(tabArgs)
            wrapper.appendChild(panel)
        })

        return wrapper
    },
}
