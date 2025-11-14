import type { Args } from '../../../../stories/RzTab.stories'

export const COMPONENT_CLASS_NAME = 'rz-tab'
export const VARIANTS = ['filled', 'underlined']

export function rzTabRenderer(args: Args) {
    const tab = document.createElement(args.tag || 'button')
    const classNames = [
        COMPONENT_CLASS_NAME,
        args.variant && `${COMPONENT_CLASS_NAME}--${args.variant}`,
        args.selected && `${COMPONENT_CLASS_NAME}--selected`,
    ].filter((c) => c) as string[]
    tab.classList.add(...classNames)
    tab.innerHTML = args.innerHTML

    if (args.attributes) {
        Object.entries(args.attributes).forEach(([key, value]) => {
            tab.setAttribute(key, value)
        })
    }

    return tab
}
