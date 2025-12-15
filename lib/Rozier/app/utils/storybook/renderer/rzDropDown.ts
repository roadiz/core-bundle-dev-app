import type { Args as ItemArgs } from '../../../../stories/RzDropdownItem.stories'
import type { Args as DropdownArgs } from '../../../../stories/RzDropdown.stories'
import { rzBadgeRenderer } from '~/utils/component-renderer/rzBadge'

const COMPONENT_CLASS_NAME = 'rz-dropdown'

export const DEFAULT_ITEM: ItemArgs = {
    tag: 'button',
    iconClass: 'rz-icon-ri--user-6-line',
    label: 'Profile X',
    description: 'Profile description',
    rightIconClass: 'rz-icon-ri--arrow-right-s-line',
    badge: {
        label: 'D',
        size: 'xs',
        iconClass: 'rz-icon-ri--command-line',
        attributes: {
            'aria-label': 'command name shorthand',
        },
    },
}

export function rzDropdownItemRenderer(
    args: ItemArgs,
    itemClass: string = `${COMPONENT_CLASS_NAME}__item`,
) {
    const item = document.createElement(args.tag || 'div')
    item.classList.add(itemClass)

    if (args.selected) {
        item.classList.add(`${itemClass}--selected`)
    }

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (key !== 'tag') {
            item.setAttribute(key, value)
        }
    })

    if (args.iconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${itemClass}__icon`, args.iconClass)
        item.appendChild(icon)
    }

    if (args.label || args.description) {
        const textWrapper = document.createElement('div')
        textWrapper.classList.add(`${itemClass}__text-wrapper`)
        item.appendChild(textWrapper)

        if (args.label) {
            const label = document.createElement('span')
            label.classList.add(`${itemClass}__label`)
            label.innerHTML = args.label
            textWrapper.appendChild(label)
        }

        if (args.description) {
            const description = document.createElement('div')
            description.classList.add(`${itemClass}__description`)
            description.innerHTML = args.description
            textWrapper.appendChild(description)
        }
    }

    if (args.badge) {
        const badge = rzBadgeRenderer(args.badge)
        badge.classList.add(`${itemClass}__badge`)
        item.appendChild(badge)
    }

    if (args.rightIconClass) {
        const icon = document.createElement('span')
        icon.classList.add(`${itemClass}__icon`, args.rightIconClass)
        item.appendChild(icon)
    }

    return item
}

function rzDropdownMenuRenderer(items: ItemArgs[], tag?: string) {
    const body = document.createElement(tag || 'menu')
    body.className = `${COMPONENT_CLASS_NAME}__list`

    items.forEach((itemArgs) => {
        const itemWrapper = document.createElement('li')
        body.appendChild(itemWrapper)

        if (itemArgs.tag === 'hr') {
            return
        }

        const item = rzDropdownItemRenderer(itemArgs)
        itemWrapper.appendChild(item)
    })

    return body
}

export function rzDropdownRenderer(args: DropdownArgs, el?: HTMLElement) {
    const wrapper = el || document.createElement('div')
    wrapper.className = COMPONENT_CLASS_NAME
    if (args.isOpen) {
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--open`)
    }
    if (args.reverse) {
        wrapper.classList.add(`${COMPONENT_CLASS_NAME}--reverse`)
    }
    if (args.borderColor) {
        wrapper.style.setProperty(
            '--rz-dropdown-border-color',
            args.borderColor,
        )
    }

    const headElements = args.displayHeadElements ? args.headElements : []
    if (args.title || headElements.length) {
        const head = document.createElement('div')
        head.className = `${COMPONENT_CLASS_NAME}__head`

        if (args.title) {
            const title = document.createElement('div')
            title.className = `${COMPONENT_CLASS_NAME}__title`
            title.innerText = args.title || ''
            head.appendChild(title)
        }

        if (headElements.length) {
            const headElementList = document.createElement('ul')
            headElementList.className = `${COMPONENT_CLASS_NAME}__info-list`
            head.appendChild(headElementList)

            headElements.forEach((el) => {
                const infoItem = document.createElement('li')
                infoItem.classList.add(`${COMPONENT_CLASS_NAME}__info-item`)
                headElementList.appendChild(infoItem)

                if (el.innerHTML) {
                    infoItem.innerHTML += el.innerHTML
                } else {
                    const element = document.createElement(el.tag || 'div')
                    Object.entries(el).forEach(([key, value]) => {
                        if (key !== 'tag') {
                            element.setAttribute(key, value)
                        }
                    })
                    infoItem.appendChild(element)
                }
            })
        }

        wrapper.appendChild(head)
    }

    args.items.forEach((bodyItems) => {
        const items = Array.isArray(bodyItems) ? bodyItems : [bodyItems]
        const body = rzDropdownMenuRenderer(items, args.listTag)
        wrapper.appendChild(body)
    })

    if (args.footerContent) {
        const footer = document.createElement('div')
        footer.className = `${COMPONENT_CLASS_NAME}__footer`
        footer.innerHTML = args.footerContent
        wrapper.appendChild(footer)
    }

    return wrapper
}
