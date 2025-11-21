import type { Meta, StoryObj } from '@storybook/html-vite'

type MenuItem = {
    tag: string
    label?: string
    iconClass?: string
    selected?: boolean
    attributes?: Record<string, string>
}

export type Args = {
    label?: string
    menuItems: MenuItem[]
}

const COMPONENT_CLASS_NAME = 'rz-menu-bar'

const meta: Meta<Args> = {
    title: 'Components/MenuBar',
    tags: ['autodocs'],
    args: {
        label: 'Menu Item',
        menuItems: [
            {
                tag: 'a',
                label: 'Content',
                attributes: {
                    href: '#',
                },
            },
            {
                tag: 'a',
                label: 'Settings',
                selected: true,
                attributes: {
                    href: '#',
                },
            },
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--more-line',
                attributes: {
                    id: 'dropdown-button-1',
                    popovertarget: 'dropdown-id-1',
                    'aria-label': 'dropdown button label',
                },
            },
            {
                tag: 'hr',
            },
            {
                tag: 'a',
                iconClass: 'rz-icon-ri--printer-line',
                attributes: {
                    href: '#',
                    'aria-label': 'print',
                },
            },
            {
                tag: 'a',
                iconClass: 'rz-icon-ri--user-shared-line',
                attributes: {
                    href: '#',
                    'aria-label': 'user shared',
                },
            },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function rzSeparatorRenderer(args: MenuItem) {
    const item = document.createElement('hr')
    item.classList.add(`${COMPONENT_CLASS_NAME}__separator`)

    if (args.attributes) {
        Object.entries(args.attributes).forEach(([key, value]) => {
            if (value) item.setAttribute(key, String(value))
        })
    }

    return item
}

function rzLinkRenderer(args: MenuItem) {
    const item = document.createElement('a')
    const className = `${COMPONENT_CLASS_NAME}__item`
    item.classList.add(className)

    if (args.selected) {
        item.classList.add(`${className}--selected`)
    }

    if (args.label) {
        item.textContent = args.label
    }

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${COMPONENT_CLASS_NAME}__icon`, args.iconClass)
        item.appendChild(icon)
    }

    if (args.attributes) {
        Object.entries(args.attributes).forEach(([key, value]) => {
            if (value) item.setAttribute(key, String(value))
        })
    }

    return item
}

// TODO: Replace with rz-popover component when ready
// Custom Popover element design should be inspired by Popover API and Anchor positioning API
function rzDropdownRenderer(args: MenuItem) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(`${COMPONENT_CLASS_NAME}__dropdown`)

    const button = document.createElement('button')
    button.classList.add(`${COMPONENT_CLASS_NAME}__item`)
    if (args.label) {
        button.textContent = args.label
    }

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${COMPONENT_CLASS_NAME}__icon`, args.iconClass)
        button.appendChild(icon)
    }

    if (args.attributes) {
        Object.entries(args.attributes).forEach(([key, value]) => {
            if (value) button.setAttribute(key, String(value))
        })
    }
    wrapper.appendChild(button)

    const dropdown = document.createElement('div')
    dropdown.classList.add(`${COMPONENT_CLASS_NAME}__dropdown__content`)
    if (args.attributes?.popovertarget) {
        // dropdown.setAttribute('popover', '')
        dropdown.setAttribute('id', args.attributes.popovertarget)
    }
    if (args.attributes?.id) {
        dropdown.setAttribute('anchor', args.attributes.id)
    }
    dropdown.textContent = 'Dropdown Content'
    wrapper.appendChild(dropdown)

    return wrapper
}

function rzMenuItemRenderer(args: MenuItem) {
    if (args.tag === 'button') {
        return rzDropdownRenderer(args)
    } else {
        return rzLinkRenderer(args)
    }
}

function rzMenuBarRenderer(args: Args) {
    const wrapper = document.createElement('nav')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    const inner = document.createElement('ul')
    inner.classList.add(`${COMPONENT_CLASS_NAME}__inner`)
    wrapper.appendChild(inner)

    args.menuItems.forEach((menuItemArgs) => {
        const item = document.createElement('li')
        inner.appendChild(item)

        if (menuItemArgs.tag === 'hr') {
            item.classList.add(`${COMPONENT_CLASS_NAME}__separator`)
        } else {
            const menuItem = rzMenuItemRenderer(menuItemArgs)
            item.appendChild(menuItem)
        }
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzMenuBarRenderer(args)
    },
}
