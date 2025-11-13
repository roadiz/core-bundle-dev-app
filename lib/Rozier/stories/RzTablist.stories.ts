import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-tablist'
const VARIANTS = ['filled', 'underlined']

type Tab = {
    label: string
    id: string
    selected?: boolean
    panel: {
        id: string
        hidden?: boolean
    }
}

type Args = {
    variant?: (typeof VARIANTS)[number]
    tabs: Tab[]
}

const DEFAULT_TABS: Tab[] = [
    {
        id: 'tab-1',
        label: 'Tab label 1',
        selected: true,
        panel: {
            id: 'panel-1',
        },
    },
    {
        id: 'tab-2',
        label: 'Tab label 2',
        panel: {
            id: 'panel-2',
        },
    },
    {
        id: 'tab-3',
        label: 'Tab label 3',
        panel: {
            id: 'panel-3',
        },
    },
]

const meta: Meta<Args> = {
    title: 'Components/Tab/Tablist',
    tags: ['autodocs'],
    args: {
        variant: 'filled',
        tabs: DEFAULT_TABS,
    },
    argTypes: {
        variant: {
            control: 'select',
            options: ['', ...VARIANTS],
        },
    },
}

export default meta
type Story = StoryObj<Args>

function tabPanelRenderer(args: Tab) {
    const tabPanel = document.createElement('div')
    tabPanel.classList.add('rz-tabpanel')
    tabPanel.setAttribute('role', 'tabpanel')
    tabPanel.setAttribute('aria-labelledby', args.id)
    tabPanel.id = args.panel.id
    if (args.panel.hidden) {
        tabPanel.setAttribute('hidden', 'true')
    }
    tabPanel.textContent = `Content for ${args.label}`

    return tabPanel
}

function rzTabRenderer(args: Tab) {
    const tab = document.createElement('button')
    tab.setAttribute('role', 'tab')
    tab.setAttribute('type', 'button')
    tab.setAttribute('aria-controls', args.panel.id)
    tab.id = args.id
    tab.textContent = args.label

    return tab
}

function rzTablistRenderer(args: Args) {
    const wrapper = document.createElement(COMPONENT_CLASS_NAME)
    const classList = [
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
        if (tabArgs.selected) {
            tab.classList.add(`${COMPONENT_CLASS_NAME}__tab--selected`)
            tab.setAttribute('aria-selected', 'true')
        } else {
            tab.setAttribute('aria-selected', 'false')
        }
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
    args: {
        variant: undefined,
    },
}
