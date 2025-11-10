import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-tablist'
const VARIANTS = ['filled', 'underlined']
type Tab = {
    label: string
    selected?: boolean
}

type Args = {
    variant?: (typeof VARIANTS)[number]
    tabs: Tab[]
}

const meta: Meta<Args> = {
    title: 'Components/Tab/TabList',
    tags: ['autodocs'],
    args: {
        variant: 'filled',
        tabs: [
            { label: 'Tab label 1', selected: true },
            { label: 'Tab label 2' },
            { label: 'Tab label 3' },
        ],
    },
    argTypes: {
        variant: {
            control: 'select',
            options: VARIANTS,
        },
    },
}

export default meta
type Story = StoryObj<Args>

function rzTabRenderer(args: Tab) {
    const tab = document.createElement('button')
    tab.setAttribute('role', 'tab')
    tab.setAttribute('type', 'button')
    tab.textContent = args.label

    return tab
}

function rzTablistRenderer(args: Args) {
    const wrapper = document.createElement('div')
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
        tab.classList.add(`${COMPONENT_CLASS_NAME}__inner__tab`)
        if (tabArgs.selected) {
            tab.classList.add(`${COMPONENT_CLASS_NAME}__inner__tab--selected`)
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
