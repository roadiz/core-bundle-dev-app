import { rzTabRenderer, VARIANTS } from './rzTab'
import type { Args as TabArgs } from '../../../../stories/RzTab.stories'

type Args = {
    tabs: TabArgs[]
    variant?: (typeof VARIANTS)[number]
}

export const COMPONENT_CLASS_NAME = 'rz-tab-wrapper'

export function rzTabWrapperRenderer(args: Args, is?: string) {
    const wrapper = document.createElement(is || 'div')
    const classList = [
        COMPONENT_CLASS_NAME,
        args.variant && `${COMPONENT_CLASS_NAME}--${args.variant}`,
    ].filter((c) => c) as string[]
    wrapper.classList.add(...classList)

    const tablist = document.createElement('div')
    tablist.classList.add(`${COMPONENT_CLASS_NAME}__inner`)
    wrapper.appendChild(tablist)

    args.tabs.forEach((tabArgs) => {
        const tab = rzTabRenderer(tabArgs)
        tab.classList.add(`${COMPONENT_CLASS_NAME}__tab`)

        tablist.appendChild(tab)
    })

    return wrapper
}
