import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzElement } from '~/utils/component-renderer/rzElement'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'

const COMPONENT_CLASS_NAME = 'rz-breadcrumb'

/**
 * Past this many ancestors the middle ones fold into a popover. Mirrors `collapse_from` in
 * templates/macros/rz_breadcrumb.html.twig — keep both in step.
 */
export const COLLAPSE_FROM = 3

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

type ItemOptions = {
    /** The current page: plain text instead of a link. */
    current?: boolean
    /**
     * A folded level: rendered as a dropdown entry. Menu entries need the hit area
     * and hover state of .rz-dropdown__item, which a trail link does not have.
     */
    inPopover?: boolean
}

function itemRenderer(item: BreadcrumbItem, options: ItemOptions = {}) {
    const { current = false, inPopover = false } = options
    const li = rzElement({
        tag: 'li',
        attributes: inPopover
            ? {}
            : { class: `${COMPONENT_CLASS_NAME}__list-item` },
    })

    // Folded levels take the dropdown classes, so the popover reads like the rz-brand quick-access
    // menu. No __text-wrapper: its 16px right margin clears a trailing icon we do not have.
    if (inPopover) {
        const link = rzElement({
            tag: 'a',
            attributes: { class: 'rz-dropdown__item', href: item.url || '#' },
        })
        link.appendChild(
            rzElement({
                tag: 'span',
                innerText: item.label,
                attributes: { class: 'rz-dropdown__item__label' },
            }),
        )
        li.appendChild(link)

        return li
    }

    const inner = rzElement({
        tag: current ? 'span' : 'a',
        innerText: item.label,
        attributes: {
            class: `${COMPONENT_CLASS_NAME}__item`,
            ...(current
                ? { 'aria-current': 'page' }
                : { href: item.url || '#' }),
        },
    })
    li.appendChild(inner)

    return li
}

/** Mirrors what rz_overflow_list.html.twig emits for the folded middle levels. */
function overflowRenderer(items: BreadcrumbItem[], ariaLabel: string) {
    const list = rzElement({
        tag: 'ul',
        attributes: { class: 'rz-dropdown__list rz-overflow-list__list' },
    })
    items.forEach((item) =>
        list.appendChild(itemRenderer(item, { inPopover: true })),
    )

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

    const li = rzElement({
        tag: 'li',
        attributes: {
            class: `rz-overflow-list ${COMPONENT_CLASS_NAME}__list-item`,
        },
    })
    li.appendChild(popover)

    return li
}

export function rzBreadcrumbRenderer(args: BreadcrumbArgs) {
    const { parents } = args
    const collapsed = parents.length > COLLAPSE_FROM

    const list = rzElement({
        tag: 'ol',
        attributes: { class: `${COMPONENT_CLASS_NAME}__list` },
    })

    if (collapsed) {
        list.appendChild(itemRenderer(parents[0]))
        list.appendChild(
            overflowRenderer(parents.slice(1, -1), args.overflowLabel),
        )
        list.appendChild(itemRenderer(parents[parents.length - 1]))
    } else {
        parents.forEach((item) => list.appendChild(itemRenderer(item)))
    }

    list.appendChild(itemRenderer({ label: args.current }, { current: true }))

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
