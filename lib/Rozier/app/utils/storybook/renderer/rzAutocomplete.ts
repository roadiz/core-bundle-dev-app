export const RZ_AUTOCOMPLETE_ACTIONS = [
    'tagAutocomplete',
    'folderAutocomplete',
] as const

export type RzAutocompleteAction = (typeof RZ_AUTOCOMPLETE_ACTIONS)[number]

export type RzAutocompleteOptions = {
    action: RzAutocompleteAction
    searchPath: string
    csrfToken?: string
    placeholder?: string
}

/*
 * <rz-autocomplete> as rendered by the node and document bulk actions
 * (nodes/bulkActions.html.twig, documents/bulkActions.html.twig): a host wrapping
 * the form whose text input gets the suggestion menu.
 */
export function rzAutocompleteRenderer(args: RzAutocompleteOptions) {
    const host = document.createElement('rz-autocomplete')
    host.setAttribute('data-action', args.action)
    host.setAttribute('data-search-path', args.searchPath)
    host.setAttribute('data-csrf-token', args.csrfToken || '')

    const form = document.createElement('form')
    form.className = 'rz-form'
    host.appendChild(form)

    const input = document.createElement('input')
    input.setAttribute('is', 'rz-input')
    input.type = 'text'
    input.className = 'rz-form-field__body rz-autocomplete__input'
    if (args.placeholder) input.placeholder = args.placeholder
    form.appendChild(input)

    return host
}
