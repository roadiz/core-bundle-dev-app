/*
 * Roadiz Rozier Bundle entry point
 */
import ColorInput from "./widgets/ColorInput.js"
import GenericBulkActions from "./widgets/GenericBulkActions.js"
import Mobile from "./behaviours/Mobile.js"
import NodeTreeContextualMenu from "./custom-elements/NodeTreeContextualMenu.js"
import NodeSourceEditPage from "./custom-elements/NodeSourceEditPage.js"
import TagEditPage from "./custom-elements/TagEditPage.js"
import AdminMenuNav from "./custom-elements/AdminMenuNav.js"
import FolderAutocomplete from "./custom-elements/FolderAutocomplete.js"

(function () {
    console.log('Roadiz Rozier Bundle entry point 🎉')

    customElements.define('node-tree-contextual-menu', NodeTreeContextualMenu)
    customElements.define('node-source-edit-page', NodeSourceEditPage)
    customElements.define('tag-edit-page', TagEditPage)
    customElements.define('admin-menu-nav', AdminMenuNav)
    customElements.define('folder-autocomplete', FolderAutocomplete)

    /*
     * init generic bulk actions widget
     */
    GenericBulkActions()
    ColorInput()
    new Mobile()
    window.addEventListener('pageshowend', () => {
        GenericBulkActions()
        ColorInput()
    })
})();
