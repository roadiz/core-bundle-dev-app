import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzHeaderItemRenderer } from '~/utils/storybook/renderer/rzHeaderItem'
import type { Args as ItemArgs } from './RzHeaderItem.stories'

const meta: Meta = {
    title: 'Components/Header',
    tags: ['autodocs'],
    args: {},
}

const COMPONENT_CLASS_NAME = 'rz-header'

export default meta
type Story = StoryObj

function quickMenuRenderer() {
    const menu = document.createElement('div')
    menu.style = `
		padding: 8px 12px;
		background-color: #f0f0f0;
		border-radius: 4px;
	`
    menu.classList.add(`${COMPONENT_CLASS_NAME}__quick-menu`)
    menu.innerText = 'R'

    return menu
}

function burgerRenderer() {
    const burger = document.createElement('input')
    burger.type = 'checkbox'

    burger.style = `
		padding: 8px 12px;
		background-color: #f0f0f0;
		border-radius: 4px;
	`
    burger.classList.add(`${COMPONENT_CLASS_NAME}__burger`)
    burger.innerText = 'Burger'

    return burger
}

function itemRenderer(itemArgs: ItemArgs) {
    const item = rzHeaderItemRenderer(itemArgs)
    item.classList.add(`${COMPONENT_CLASS_NAME}__item`)
    return item
}

function menuRenderer() {
    const menu = document.createElement('ul')
    menu.classList.add(`${COMPONENT_CLASS_NAME}__list`)
    ;[
        {
            iconClass: 'rz-icon-ri--dashboard-line',
            label: 'Dashboard',
        },
        {
            iconClass: 'rz-icon-ri--calendar-event-line',
            label: 'Events',
        },
        {
            iconClass: 'rz-icon-ri--image-line',
            label: 'Documents',
        },
        {
            iconClass: 'rz-icon-ri--computer-line',
            label: 'Main menu',
        },
    ].forEach((itemArgs) => {
        const listItem = document.createElement('li')

        const item = itemRenderer(itemArgs)
        listItem.appendChild(item)
        menu.appendChild(listItem)
    })

    return menu
}

export const Default: Story = {
    render: () => {
        const header = document.createElement('header')
        header.classList.add(COMPONENT_CLASS_NAME)

        const head = document.createElement('div')
        head.classList.add(`${COMPONENT_CLASS_NAME}__head`)
        header.appendChild(head)

        const quickMenu = quickMenuRenderer()
        head.appendChild(quickMenu)

        const burger = burgerRenderer()
        burger.classList.add('burger-menu-input')
        head.appendChild(burger)

        const nav = document.createElement('nav')
        nav.classList.add(`${COMPONENT_CLASS_NAME}__nav`)
        header.appendChild(nav)

        nav.appendChild(
            itemRenderer({
                label: 'Search',
                iconClass: 'rz-icon-ri--search-line',
            }),
        ).classList.add(`${COMPONENT_CLASS_NAME}__item--start`)
        nav.appendChild(menuRenderer())
        nav.appendChild(
            itemRenderer({
                label: 'Settings',
                iconClass: 'rz-icon-ri--settings-4-line',
            }),
        ).classList.add(`${COMPONENT_CLASS_NAME}__item--end`)

        return header
    },
}
