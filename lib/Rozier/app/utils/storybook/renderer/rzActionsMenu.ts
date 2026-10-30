import {
    type RzButtonOptions,
    rzButtonRenderer,
} from '~/utils/component-renderer/rzButton'

export type RzActionsMenuItemOptions = {
    /** HTML of an element with `popover`, opened by the button (rz-popover wrapper). */
    popoverContent?: string
    button: RzButtonOptions
}

export type RzActionsMenuOptions = {
    vertical?: boolean
    class?: string
    items: RzActionsMenuItemOptions[]
}

const COMPONENT_CLASS_NAME = 'rz-actions-menu'

/** Mirrors components/rz_actions_menu.html.twig. */
export function rzActionsMenuRenderer(args: RzActionsMenuOptions) {
    const element = document.createElement('rz-actions-menu')
    element.classList.add(COMPONENT_CLASS_NAME)
    if (args.vertical) {
        element.classList.add(`${COMPONENT_CLASS_NAME}--vertical`)
    }
    if (args.class) {
        element.classList.add(...args.class.split(' ').filter(Boolean))
    }
    element.setAttribute('data-axis', 'y')

    args.items.forEach((item) => {
        const button = rzButtonRenderer(item.button)

        if (!item.popoverContent) {
            element.appendChild(button)
            return
        }

        const popover = document.createElement('rz-popover')
        popover.setAttribute('data-popover-offset', '24px')
        popover.setAttribute('data-popover-placement', 'top-start')
        popover.innerHTML = item.popoverContent
        popover.appendChild(button)
        element.appendChild(popover)
    })

    const handle = document.createElement('button')
    handle.classList.add(`${COMPONENT_CLASS_NAME}__handle`)
    handle.type = 'button'
    handle.setAttribute('aria-label', 'Drag actions menu')
    handle.innerHTML = '<span class="rz-icon-rz--draggable-2"></span>'
    element.appendChild(handle)

    return element
}
