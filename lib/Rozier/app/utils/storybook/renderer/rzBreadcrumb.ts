import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzElement } from '~/utils/component-renderer/rzElement'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'

const COMPONENT_CLASS_NAME = 'rz-breadcrumb'

/**
 * At most this many ancestors stay in the trail, the levels after the root fold into a popover
 * past that. Mirrors `max_visible` in templates/macros/rz_breadcrumb.html.twig — keep both in step.
 */
export const MAX_VISIBLE = 6

export type BreadcrumbItem = {
    label: string
    url?: string
}

export type BreadcrumbArgs = {
    /** Ancestors, from the root down. The current page is not one of them. */
    parents: BreadcrumbItem[]
    current: string
    ariaLabel: string
    overflowLabel: string
}

/** Same markup in the trail and in the popover: the responsive collapse moves the <li> as is. */
function itemRenderer(item: BreadcrumbItem, current = false) {
    const li = rzElement({
        tag: 'li',
        attributes: { class: `${COMPONENT_CLASS_NAME}__list-item` },
    })
    li.appendChild(
        rzElement({
            tag: current ? 'span' : 'a',
            innerText: item.label,
            attributes: {
                class: `${COMPONENT_CLASS_NAME}__item`,
                ...(current
                    ? { 'aria-current': 'page' }
                    : { href: item.url || '#' }),
            },
        }),
    )

    return li
}

/** Mirrors what rz_overflow_list.html.twig emits with `responsive` on. */
function overflowRenderer(
    folded: BreadcrumbItem[],
    inlineCount: number,
    ariaLabel: string,
) {
    const list = rzElement({
        tag: 'ul',
        attributes: { class: 'rz-dropdown__list rz-overflow-list__list' },
    })
    folded.forEach((item) => list.appendChild(itemRenderer(item)))

    const content = rzElement({
        attributes: { class: 'rz-dropdown rz-overflow-list__popover' },
    })
    content.appendChild(list)

    const { popover } = rzPopoverRenderer({
        targetElement: {
            element: rzButtonRenderer({
                iconClass: 'rz-icon-ri--more-line',
                emphasis: 'tertiary',
                size: 'sm',
                attributes: {
                    type: 'button',
                    'aria-label': ariaLabel,
                    class: 'rz-overflow-list__button',
                },
            }),
        },
        popoverElement: { element: content, id: 'rz-breadcrumb-overflow' },
        placement: 'bottom-start',
        offset: 8,
    })
    popover.setAttribute('overflow-responsive', '')
    popover.setAttribute('overflow-inline', String(inlineCount))

    const li = rzElement({
        tag: 'li',
        attributes: {
            class: `rz-overflow-list ${COMPONENT_CLASS_NAME}__list-item`,
        },
    })
    li.hidden = folded.length === 0
    li.appendChild(popover)

    return li
}

export function rzBreadcrumbRenderer(args: BreadcrumbArgs) {
    const { parents } = args
    // Root and last ancestor never fold; everything in between is the popover's to manage.
    const middle = parents.length > 2 ? parents.slice(1, -1) : []
    const foldedCount = Math.max(0, parents.length - MAX_VISIBLE)

    const list = rzElement({
        tag: 'ol',
        attributes: { class: `${COMPONENT_CLASS_NAME}__list` },
    })

    if (middle.length === 0) {
        parents.forEach((item) => list.appendChild(itemRenderer(item)))
    } else {
        list.appendChild(itemRenderer(parents[0]))
        list.appendChild(
            overflowRenderer(
                middle.slice(0, foldedCount),
                middle.length - foldedCount,
                args.overflowLabel,
            ),
        )
        middle
            .slice(foldedCount)
            .forEach((item) => list.appendChild(itemRenderer(item)))
        list.appendChild(itemRenderer(parents[parents.length - 1]))
    }

    list.appendChild(itemRenderer({ label: args.current }, true))

    const nav = rzElement({
        tag: 'nav',
        attributes: {
            class: COMPONENT_CLASS_NAME,
            'aria-label': args.ariaLabel,
        },
    })
    nav.appendChild(list)

    return nav
}
