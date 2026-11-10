import { MultiValueAutocomplete } from '~/utils/autocomplete/MultiValueAutocomplete'

export default class TagAutocomplete extends MultiValueAutocomplete {
    protected get inputSelector() {
        return '.rz-tag-autocomplete'
    }

    protected get action() {
        return 'tagAutocomplete'
    }
}
