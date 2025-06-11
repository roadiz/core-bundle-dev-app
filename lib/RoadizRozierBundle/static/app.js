/*
 * Roadiz Rozier Bundle entry point
 */
import ColorInput from './widgets/ColorInput.js'
import GenericBulkActions from './widgets/GenericBulkActions.js'
import Mobile from './behaviours/Mobile.js'
import NodeTreeContextualMenu from './custom-elements/NodeTreeContextualMenu.js'
import NodeSourceEditPage from './custom-elements/NodeSourceEditPage.js'
import AdminMenuNav from './custom-elements/AdminMenuNav.js'

(function () {
    console.log('Roadiz Rozier Bundle entry point 🎉')

    customElements.define('node-tree-contextual-menu', NodeTreeContextualMenu)
    customElements.define('node-source-edit-page', NodeSourceEditPage)
    customElements.define('admin-menu-nav', AdminMenuNav)

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
})()
