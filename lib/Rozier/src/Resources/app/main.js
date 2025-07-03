import 'jquery.actual'
import './vendor/heartcode-canvasloader-min'
import './vendor/jquery.caret.min'
import './vendor/jquery.tag-editor'
import './vendor/jquery.collection'

import 'uikit/dist/js/uikit'
import 'uikit/dist/js/components/nestable'
import 'uikit/dist/js/components/sortable'
import 'uikit/dist/js/components/datepicker'
import 'uikit/dist/js/components/pagination'
import 'uikit/dist/js/components/notify'
import 'uikit/dist/js/components/tooltip'

import CodeMirror from 'codemirror'
import 'codemirror/mode/markdown/markdown'
import 'codemirror/mode/javascript/javascript'
import 'codemirror/mode/css/css'
import 'codemirror/addon/mode/overlay'
import 'codemirror/mode/xml/xml'
import 'codemirror/mode/yaml/yaml'
import 'codemirror/mode/gfm/gfm'
import 'codemirror/addon/display/rulers'

import 'jquery-ui'
import 'jquery-ui/ui/widgets/autocomplete'

import ColorInput from './behaviours/ColorInput'
import GenericBulkActions from './behaviours/GenericBulkActions'
import Mobile from './behaviours/Mobile'
import Save from './behaviours/Save'
import NodeTreeContextualMenu from './custom-elements/NodeTreeContextualMenu'
import NodeSourceEditPage from './custom-elements/NodeSourceEditPage'
import TagEditPage from './custom-elements/TagEditPage'
import AdminMenuNav from './custom-elements/AdminMenuNav'
import FolderAutocomplete from './custom-elements/FolderAutocomplete'
import TagAutocomplete from './custom-elements/TagAutocomplete'
import MainTrees from './custom-elements/MainTrees'
import Rozier from './Rozier'

window.CodeMirror = CodeMirror
window.UIkit = UIkit
/*
 * ============================================================================
 * Rozier entry point
 * ============================================================================
 */
window.Rozier = new Rozier()
const ready = (callback) => {
    if (document.readyState !== 'loading') callback()
    else document.addEventListener('DOMContentLoaded', callback)
}
ready(() => {
    window.Rozier.onDocumentReady()
})
;(function () {
    /*
     * Defining custom HTML elements
     */
    customElements.define('node-tree-contextual-menu', NodeTreeContextualMenu)
    customElements.define('node-source-edit-page', NodeSourceEditPage)
    customElements.define('tag-edit-page', TagEditPage)
    customElements.define('admin-menu-nav', AdminMenuNav)
    customElements.define('folder-autocomplete', FolderAutocomplete)
    customElements.define('tag-autocomplete', TagAutocomplete)
    customElements.define('main-trees', MainTrees)

    /*
     * init generic bulk actions widget
     */
    GenericBulkActions()
    ColorInput()
    new Mobile()
    new Save()

    window.addEventListener('pageshowend', () => {
        GenericBulkActions()
        ColorInput()
        new Save()
    })
})()
