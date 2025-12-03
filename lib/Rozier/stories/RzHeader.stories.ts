import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzHeaderNavItemRenderer } from '~/utils/storybook/renderer/rzHeaderNavItem'
import type { Args as ItemArgs } from './RzHeaderNavItem.stories'
import { rzBadgeRenderer } from '~/utils/storybook/renderer/rzBadge'
import { rzButtonRenderer } from '~/utils/storybook/renderer/rzButton'

type navItem = ItemArgs & {
    children?: ItemArgs[]
    additionalClass?: string
}

type Args = {
    navItems: navItem[]
}

const meta: Meta<Args> = {
    title: 'Components/Header',
    tags: ['autodocs'],
    args: {
        navItems: [
            {
                label: 'Search',
                iconClass: 'rz-icon-ri--search-line',
            },
            {
                tag: 'button',
                attributes: {
                    is: 'rz-header-nav-button',
                },
                label: 'Dashboard',
                iconClass: 'rz-icon-ri--dashboard-line',
                children: [
                    {
                        tag: 'a',
                        label: 'Sub item 1',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 2',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 3',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 4',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 5',
                        attributes: { href: '#' },
                    },
                ],
            },
            {
                tag: 'button',
                attributes: {
                    is: 'rz-header-nav-button',
                },
                label: 'Events',
                iconClass: 'rz-icon-ri--calendar-event-line',
                children: [
                    {
                        tag: 'a',
                        label: 'Sub item 1',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 2',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 3',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 4',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 5',
                        attributes: { href: '#' },
                    },
                ],
            },
            {
                tag: 'a',
                attributes: { href: '#' },
                label: 'Documents',
                iconClass: 'rz-icon-ri--image-line',
            },
            {
                tag: 'a',
                attributes: { href: '#' },
                label: 'Main menu',
                iconClass: 'rz-icon-ri--computer-line',
            },
            {
                tag: 'button',
                attributes: {
                    is: 'rz-header-nav-button',
                },
                label: 'Settings',
                iconClass: 'rz-icon-ri--settings-4-line',
                additionalClass: 'rz-header__item--end',
                children: [
                    {
                        tag: 'a',
                        label: 'Sub item 1',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 2',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 3',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 4',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 5',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 1',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 2',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 3',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 4',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Sub item 5',
                        attributes: { href: '#' },
                    },
                ],
            },
        ],
    },
}

const COMPONENT_CLASS_NAME = 'rz-header'

export default meta
type Story = StoryObj<Args>

function brandItemRenderer() {
    const menu = document.createElement('div')
    menu.classList.add(`${COMPONENT_CLASS_NAME}__brand`)
    menu.innerText = 'R'

    return menu
}

function quickMenuRenderer() {
    const wrapper = document.createElement('div')
    wrapper.classList.add(`${COMPONENT_CLASS_NAME}__quick-menu`)
    const target = rzButtonRenderer({
        iconClass: 'rz-icon-ri--more-line',
        emphasis: 'tertiary',
        size: 'md',
    })

    const popover = document.createElement('div')
    popover.setAttribute('popover', 'dialog')
    popover.classList.add('rz-popover')
    popover.textContent = 'Quick menu content'

    wrapper.appendChild(target)
    return wrapper
}

function burgerRenderer() {
    const field = document.createElement('div')
    field.classList.add(`${COMPONENT_CLASS_NAME}__burger`)

    const icon = document.createElement('span')
    icon.classList.add('rz-icon-ri--menu-line')
    field.appendChild(icon)

    const burger = document.createElement('input')
    burger.name = 'burger-menu'
    burger.id = 'burger-menu'
    burger.type = 'checkbox'
    field.appendChild(burger)

    return field
}

function itemRenderer(itemArgs: navItem) {
    const listItem = document.createElement('li')
    listItem.classList.add(`${COMPONENT_CLASS_NAME}__item`)

    if (itemArgs.additionalClass) {
        listItem.classList.add(itemArgs.additionalClass)
    }

    const item = rzHeaderNavItemRenderer(itemArgs)
    listItem.appendChild(item)

    if (itemArgs.children?.length) {
        const items = itemArgs.children.map(
            (childArgs) =>
                ({
                    ...childArgs,
                    variants: 'level-2',
                }) as ItemArgs,
        )
        const subList = listRenderer(items)
        subList.classList.add(`${COMPONENT_CLASS_NAME}__sub-list`)
        listItem.appendChild(subList)
    }

    return listItem
}

function listRenderer(items: Args['navItems']) {
    const list = document.createElement('ul')
    list.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    items.forEach((itemArgs) => {
        const item = itemRenderer(itemArgs)
        list.appendChild(item)
    })

    return list
}

function rzHeaderHeadRenderer() {
    const head = document.createElement('div')
    head.classList.add(`${COMPONENT_CLASS_NAME}__head`)

    const brand = brandItemRenderer()
    head.appendChild(brand)

    const quickMenu = quickMenuRenderer()
    head.appendChild(quickMenu)

    const burger = burgerRenderer()
    head.appendChild(burger)

    return head
}

export const Default: Story = {
    render: (args) => {
        const header = document.createElement('header')
        header.classList.add(COMPONENT_CLASS_NAME)

        const head = rzHeaderHeadRenderer()
        header.appendChild(head)

        const badge = rzBadgeRenderer({ label: 'Production', size: 'sm' })
        badge.classList.add(`${COMPONENT_CLASS_NAME}__head__item--end`)
        head.appendChild(badge)

        const nav = document.createElement('nav')
        nav.classList.add(`${COMPONENT_CLASS_NAME}__nav`)
        header.appendChild(nav)

        const list = listRenderer(args.navItems)
        nav.appendChild(list)

        return header
    },
}
