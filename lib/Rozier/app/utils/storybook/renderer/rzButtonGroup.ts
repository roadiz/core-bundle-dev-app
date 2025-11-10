import { type Args } from '../../../../stories/RzButtonGroup.stories'
import { rzButtonRenderer } from '~/utils/storybook/renderer/rzButton'

export const COMPONENT_CLASS_NAME = 'rz-button-group'

export function rzButtonGroupRenderer(args: Args) {
    const wrapper = document.createElement('div')
    const classList = [
        COMPONENT_CLASS_NAME,
        args.spacing && `${COMPONENT_CLASS_NAME}--${args.spacing}`,
        args.collapsed && `${COMPONENT_CLASS_NAME}--collapsed`,
    ].filter((c) => c) as string[]

    wrapper.classList.add(...classList)

    args.buttons?.forEach((buttonArgs) => {
        const buttonElement = rzButtonRenderer(buttonArgs)
        buttonElement.classList.add(`${COMPONENT_CLASS_NAME}__button`)
        wrapper.appendChild(buttonElement)
    })

    return wrapper
}
