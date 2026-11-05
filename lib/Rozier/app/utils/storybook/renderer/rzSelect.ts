export const COMPONENT_CLASS = 'rz-select'

export type RzSelectOption = {
    value: string
    label: string
    disabled?: boolean
    selected?: boolean
    hidden?: boolean
}

export type RzSelectOptionGroup = {
    label: string
    options: RzSelectOption[]
}

export type RzSelectOptions = {
    options: (RzSelectOption | RzSelectOptionGroup)[]
    required?: boolean
    attributes?: Record<string, string>
}

function rzSelectOptionRenderer(optionData: RzSelectOption) {
    const option = document.createElement('option')
    option.value = optionData.value
    option.textContent = optionData.label
    if (optionData.disabled) option.disabled = true
    if (optionData.selected) option.setAttribute('selected', '')
    if (optionData.hidden) option.hidden = true

    return option
}

export function rzSelectRenderer(args: RzSelectOptions) {
    const select = document.createElement('select', { is: COMPONENT_CLASS })
    select.setAttribute('is', COMPONENT_CLASS)

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (typeof value === 'undefined') return
        select.setAttribute(key, String(value))
    })

    select.required = args.required ?? false

    args.options.forEach((item) => {
        if ('options' in item) {
            const group = document.createElement('optgroup')
            group.label = item.label
            item.options.forEach((optionData) => {
                group.appendChild(rzSelectOptionRenderer(optionData))
            })
            select.appendChild(group)
            return
        }

        select.appendChild(rzSelectOptionRenderer(item))
    })

    return select
}
