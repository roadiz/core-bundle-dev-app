import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import type { Args } from '../../../../stories/RzFieldset.stories'

export const COMPONENT_CLASS_NAME = 'rz-fieldset'

export function rzFieldsetRenderer(args: Args) {
    const fieldset = document.createElement('fieldset')
    fieldset.classList.add(COMPONENT_CLASS_NAME)
    if (args.horizontal) {
        fieldset.classList.add(`${COMPONENT_CLASS_NAME}--horizontal`)
    }

    const legend = document.createElement('legend')
    legend.classList.add(`${COMPONENT_CLASS_NAME}__legend`)
    legend.textContent = args.legend
    fieldset.appendChild(legend)

    const fields = args.formFieldsData || []
    fields.forEach((fieldData) => {
        if ('formFieldsData' in fieldData) {
            // Nested fieldset
            const nestedFieldset = rzFieldsetRenderer(fieldData as Args)
            fieldset.appendChild(nestedFieldset)
            return
        } else if ('label' in fieldData) {
            const field = rzFormFieldRenderer(fieldData)
            fieldset.appendChild(field)
        }
    })

    return fieldset
}
