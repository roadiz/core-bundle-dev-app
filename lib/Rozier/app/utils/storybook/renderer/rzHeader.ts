import type { Args } from '../../../../stories/RzHeader.stories'
import { rzHeaderNavItemRenderer } from '~/utils/storybook/renderer/rzHeaderNavItem'
import { rzBadgeRenderer } from '~/utils/storybook/renderer/rzBadge'
import { rzBrandRenderer } from '~/utils/storybook/renderer/rzBrand'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'
import { rzDropdownRenderer } from '~/utils/storybook/renderer/rzDropDown'

const COMPONENT_CLASS_NAME = 'rz-header'

export const DEFAULT_NAV_ITEMS = [
    {
        label: 'Search',
        iconClass: 'rz-icon-ri--search-line',
    },
    {
        tag: 'button',
        attributes: {
            is: 'rz-header-nav-item-button',
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
            is: 'rz-header-nav-item-button',
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
            is: 'rz-header-nav-item-button',
        },
        label: 'Settings',
        iconClass: 'rz-icon-ri--settings-4-line',
        additionalClass: 'rz-header__li--end',
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
]

function itemRenderer(itemArgs: Args['navItems'][number]) {
    const listItem = document.createElement('li')
    listItem.classList.add(`${COMPONENT_CLASS_NAME}__li`)

    if (itemArgs.additionalClass) {
        listItem.classList.add(itemArgs.additionalClass)
    }

    const item = rzHeaderNavItemRenderer(itemArgs)
    listItem.appendChild(item)

    if (itemArgs.children?.length) {
        const subList = listRenderer(itemArgs.children, true)
        subList.classList.add(`${COMPONENT_CLASS_NAME}__sub-list`)
        listItem.appendChild(subList)
    }

    return listItem
}

function listRenderer(items: Args['navItems'], subList?: boolean) {
    const list = document.createElement('ul')
    list.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    items.forEach((itemArgs) => {
        const item = itemRenderer({ ...itemArgs, subItem: subList })
        list.appendChild(item)
    })

    return list
}

function getQuickAccessPopoverElement() {
    const { popover, popoverContent } = rzPopoverRenderer({
        placement: 'bottom-start',
        offset: 8,
        popoverElement: {
            tag: 'nav',
            id: 'QuickAccessNav',
        },
        targetElement: {
            element: rzBrandRenderer({
                tag: 'button',
                iconClass: 'rz-icon-rz--logo-rz',
                attributes: {
                    'aria-label': 'Open quick access navigation',
                },
            }),
        },
    })

    rzDropdownRenderer(
        {
            listTag: 'ul',
            items: [
                [
                    {
                        tag: 'a',
                        label: 'Label',
                        iconClass: 'rz-icon-ri--user-6-line',
                        rightIconClass: 'rz-icon-ri--arrow-right-up-line',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'a',
                        label: 'Label 2',
                        iconClass: 'rz-icon-ri--user-6-line',
                        rightIconClass: 'rz-icon-ri--arrow-right-s-line',
                        attributes: { href: '#' },
                    },
                    {
                        tag: 'button',
                        label: 'Label 3',
                        iconClass: 'rz-icon-ri--user-6-line',
                        attributes: {
                            'aria-label': 'Label for this button action',
                        },
                    },
                    {
                        tag: 'a',
                        label: 'Label 4',
                        iconClass: 'rz-icon-ri--user-6-line',
                        rightIconClass: 'rz-icon-ri--arrow-right-s-line',
                        attributes: { href: '#' },
                    },
                ],
            ],
        },
        popoverContent,
    )

    return popover
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

function rzHeaderHeadRenderer() {
    const head = document.createElement('div')
    head.classList.add(`${COMPONENT_CLASS_NAME}__head`)

    const quickAccessPopover = getQuickAccessPopoverElement()
    head.appendChild(quickAccessPopover)

    const burger = burgerRenderer()
    head.appendChild(burger)

    const badge = rzBadgeRenderer({ label: 'Production', size: 'sm' })
    badge.classList.add(`${COMPONENT_CLASS_NAME}__head__item--end`)
    head.appendChild(badge)

    return head
}

export function rzHeaderRenderer(args: Args) {
    const wrapper = document.createElement('header')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    const head = rzHeaderHeadRenderer()
    wrapper.appendChild(head)

    const nav = document.createElement('nav')
    nav.classList.add(`${COMPONENT_CLASS_NAME}__nav`)
    wrapper.appendChild(nav)

    const list = listRenderer(args.navItems)
    nav.appendChild(list)

    return wrapper
}
