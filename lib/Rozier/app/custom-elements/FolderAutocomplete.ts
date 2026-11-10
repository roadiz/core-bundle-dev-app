import { MultiValueAutocomplete } from '~/utils/autocomplete/MultiValueAutocomplete'

export default class FolderAutocomplete extends MultiValueAutocomplete {
    protected get inputSelector() {
        return '.rz-folder-autocomplete'
    }

    protected get action() {
        return 'folderAutocomplete'
    }
}
