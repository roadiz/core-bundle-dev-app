import {
    Popover,
    type PopoverOptions,
    ATTRIBUTES_OPTIONS_MAP as POPOVER_ATTRIBUTES_OPTIONS_MAP,
} from '~/utils/Popover'
import { uniqueId } from 'lodash'

export const ATTRIBUTES_OPTIONS_MAP = {
    text: 'tooltip-text',
    ...POPOVER_ATTRIBUTES_OPTIONS_MAP,
} as const
export const ATTRIBUTES_OPTIONS = Object.values(ATTRIBUTES_OPTIONS_MAP)

type TooltipOptions = PopoverOptions

export class Tooltip {
    context: HTMLElement
    options: TooltipOptions
    popoverInstance: Popover | null = null

    constructor(context: HTMLElement, options?: TooltipOptions) {
        this.context = context
        this.options = options
    }

    createTargetElement() {
        const targetElement = document.createElement('button')
        targetElement.style.all = 'unset'
        targetElement.classList.add('rz-tooltip__target')
        targetElement.innerHTML = this.context.innerHTML
        return targetElement
    }

    createTooltipElement(text: string) {
        const generatedTooltip = document.createElement('div')
        generatedTooltip.classList.add('rz-tooltip__content')
        generatedTooltip.setAttribute('popover', 'hint')
        generatedTooltip.setAttribute('role', 'tooltip')
        generatedTooltip.textContent = text

        return generatedTooltip
    }

    init() {
        this.setDefaultOption()

        const textContent = this.context.getAttribute(
            ATTRIBUTES_OPTIONS_MAP.text,
        )
        let targetElement: HTMLElement | null = this.context.querySelector(
            'button[popovertarget]',
        )
        const popoverId =
            targetElement?.getAttribute('popovertarget') ||
            uniqueId('rz-tooltip-')
        const popoverElement = this.context.querySelector('[popover]')

        if (!targetElement) {
            targetElement = this.createTargetElement()
            targetElement.setAttribute('popovertarget', popoverId)
            this.context.innerHTML = targetElement.outerHTML
        }

        if (textContent && !popoverElement) {
            const generatedTooltip = this.createTooltipElement(textContent)
            generatedTooltip.id = popoverId
            this.context.appendChild(generatedTooltip)
        }

        this.popoverInstance = new Popover(this.context, this.options)
        this.popoverInstance.init()
    }

    setDefaultOption() {
        if (!this.context.hasAttribute(ATTRIBUTES_OPTIONS_MAP.placement)) {
            this.context.setAttribute(ATTRIBUTES_OPTIONS_MAP.placement, 'top')
        }

        if (!this.context.hasAttribute(ATTRIBUTES_OPTIONS_MAP.offset)) {
            this.context.setAttribute(ATTRIBUTES_OPTIONS_MAP.offset, '4')
        }
    }

    updateOptions() {
        this.popoverInstance?.updateOptions()
    }

    destroy() {
        this.popoverInstance?.destroy()
        this.popoverInstance = null
    }
}
