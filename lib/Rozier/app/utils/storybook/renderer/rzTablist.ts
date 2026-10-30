export const TABLIST_CLASS_NAME = 'rz-tablist'
export const TABLIST_ITEM_CLASS_NAME = 'rz-tablist__item'

export type RzTablistItemOptions = {
    innerHTML: string
    selected?: boolean
    tag?: string
    attributes?: Record<string, string>
    panel?: {
        id: string
        hidden?: boolean
    }
}

export type RzTablistOptions = {
    tabs: RzTablistItemOptions[]
}

export function rzTablistItemRenderer(args: RzTablistItemOptions) {
    const tab = document.createElement(args.tag || 'button')
    const classNames = [
        TABLIST_ITEM_CLASS_NAME,
        args.selected && `${TABLIST_ITEM_CLASS_NAME}--selected`,
    ].filter((c) => c) as string[]
    tab.classList.add(...classNames)
    tab.innerHTML = args.innerHTML
    tab.setAttribute('role', 'tab')
    if (args.panel) {
        tab.setAttribute('aria-controls', args.panel.id)
    }
    if (args.selected) {
        tab.setAttribute('aria-selected', 'true')
    }
    if (args.attributes) {
        Object.entries(args.attributes).forEach(([key, value]) => {
            tab.setAttribute(key, value)
        })
    }

    return tab
}

export function rzTablistRenderer(args: RzTablistOptions) {
    const wrapper = document.createElement('rz-tablist')
    wrapper.classList.add(TABLIST_CLASS_NAME)

    args.tabs.forEach((tabArgs) => {
        const tab = rzTablistItemRenderer(tabArgs)
        wrapper.appendChild(tab)
    })

    return wrapper
}
