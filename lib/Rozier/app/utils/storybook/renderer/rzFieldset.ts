export const COMPONENT_CLASS_NAME = 'rz-fieldset'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import type { Args } from '../../../../stories/RzFieldset.stories'

export function rzFieldsetRenderer(args: Args) {
    const fieldset = document.createElement('fieldset')

    const fieldsetClasses = [COMPONENT_CLASS_NAME].filter((c) => c) as string[]
    fieldset.classList.add(...fieldsetClasses)

    const legend = document.createElement('legend')
    legend.classList.add(`${COMPONENT_CLASS_NAME}__legend`)
    legend.textContent = args.legend
    fieldset.appendChild(legend)

    const fields = args.formFieldsData || []
    fields.forEach((fieldData) => {
        const input = rzFormFieldRenderer(fieldData)
        fieldset.appendChild(input)
    })

    return fieldset
}
