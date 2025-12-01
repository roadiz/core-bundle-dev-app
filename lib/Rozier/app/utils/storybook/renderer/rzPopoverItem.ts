import type { Args } from '../../../../stories/RzPopoverItem.stories'
import { rzBadgeRenderer } from './rzBadge'

export const DEFAULT_ITEM: Args = {
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

export function rzPopoverItemRenderer(
    args: Args,
    itemClass: string = 'rz-popover-item',
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
