import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-breadcrumb'

type BreadcrumbItem = {
    innerText?: string
    attributes?: Record<string, string>
    children?: BreadcrumbItem[]
}

type Args = {
    items: BreadcrumbItem[]
}

const meta: Meta<Args> = {
    title: 'Components/Breadcrumb',
    tags: ['autodocs'],
    args: {
        items: [
            { innerText: 'Home', attributes: { href: '#' } },
            { innerText: 'Category', attributes: { href: '#' } },
            { innerText: 'Subcategory', attributes: { href: '#' } },
            {
                innerText: 'Current Page',
                attributes: { 'aria-current': 'page' },
            },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function itemRenderer(item: BreadcrumbItem) {
    const link = document.createElement(item.attributes?.href ? 'a' : 'span')
    link.classList.add(`${COMPONENT_CLASS_NAME}__item`)
    link.innerText = item.innerText

    if (item.attributes) {
        for (const [key, value] of Object.entries(item.attributes)) {
            link.setAttribute(key, value)
        }
    }
    return link
}

function rzBreadcrumbRenderer(args: Args) {
    const nav = document.createElement('nav')
    nav.classList.add(COMPONENT_CLASS_NAME)
    nav.setAttribute('aria-label', "Fil d'arianne principal")

    const ol = document.createElement('ol')
    ol.classList.add(`${COMPONENT_CLASS_NAME}__list`)
    nav.appendChild(ol)

    args.items.forEach((item) => {
        const li = document.createElement('li')
        li.classList.add(`${COMPONENT_CLASS_NAME}__list-item`)
        ol.appendChild(li)

        if (item.children && item.children.length > 0) {
            const popover = document.createElement('rz-popover')
            popover.setAttribute('data-popover-placement', 'bottom-start')
            popover.setAttribute('data-popover-offset', '12px')
            popover.classList.add(`${COMPONENT_CLASS_NAME}__dropdown`)

            const listId = 'popover-1'
            const target = document.createElement('button')
            target.classList.add('rz-button', 'rz-button--xs')
            target.innerHTML =
                '<span class="rz-button__icon rz-icon-ri--more-line"></span>'
            target.setAttribute('popovertarget', listId)
            popover.appendChild(target)

            const list = document.createElement('div')
            list.classList.add(`${COMPONENT_CLASS_NAME}__popover-content`)
            list.id = listId
            list.setAttribute('popover', 'auto')
            popover.appendChild(list)

            item.children.forEach((item) => {
                const itemElement = itemRenderer(item)
                list.appendChild(itemElement)
            })

            li.appendChild(popover)
        } else {
            const itemElement = itemRenderer(item)
            li.appendChild(itemElement)
        }
    })

    return nav
}

export const Default: Story = {
    render: (args) => {
        return rzBreadcrumbRenderer(args)
    },
}

export const WithPopover: Story = {
    render: (args) => {
        return rzBreadcrumbRenderer(args)
    },
    args: {
        items: [
            { innerText: 'Home', attributes: { href: '#' } },
            { innerText: 'Category', attributes: { href: '#' } },
            { innerText: 'Subcategory', attributes: { href: '#' } },
            {
                children: [
                    { innerText: 'Hidden item 1', attributes: { href: '#' } },
                    { innerText: 'Hidden item 2', attributes: { href: '#' } },
                ],
            },
            {
                innerText: 'Current Page',
                attributes: { 'aria-current': 'page', href: '#' },
            },
        ],
    },
}
