export type Args = {
    targetElement?: { tag?: string }
    popoverElement: { tag?: string; id: string }
    placement?: string
    offset?: number
    shift?: number
}

const COMPONENT_CLASS_NAME = 'rz-popover'

export function rzPopoverRenderer(args: Args) {
    const popover = document.createElement(COMPONENT_CLASS_NAME)
    if (args.placement) {
        popover.setAttribute('popover-placement', args.placement)
    }
    if (args.offset) {
        popover.setAttribute('popover-offset', args.offset.toString())
    }
    if (args.shift) {
        popover.setAttribute('popover-shift', args.shift.toString())
    }

    const id = args.popoverElement.id || 'popover-element'

    const target = document.createElement(args.targetElement?.tag || 'button')
    target.setAttribute('popovertarget', id)
    popover.appendChild(target)

    const popoverContent = document.createElement(
        args.popoverElement.tag || 'div',
    )
    popoverContent.setAttribute('popover', '')
    popoverContent.id = id
    popover.appendChild(popoverContent)

    return { popover, target, popoverContent }
}
