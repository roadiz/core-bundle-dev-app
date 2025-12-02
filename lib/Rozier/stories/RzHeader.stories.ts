import type { Meta, StoryObj } from '@storybook/html-vite'

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
    menu.innerText = 'Quick Menu'

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

function itemRenderer(label: string) {
    const item = document.createElement('button')
    item.classList.add(`${COMPONENT_CLASS_NAME}__list__item`)
    item.innerText = label
    return item
}

function menuRenderer() {
    const menu = document.createElement('ul')
    menu.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    Array.from({ length: 4 }).forEach((_, index) => {
        const listItem = document.createElement('li')

        const item = itemRenderer(`Menu Item ${index + 1}`)
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

        nav.appendChild(itemRenderer('Search'))
        nav.appendChild(menuRenderer())
        nav.appendChild(itemRenderer('Settings'))

        return header
    },
}
