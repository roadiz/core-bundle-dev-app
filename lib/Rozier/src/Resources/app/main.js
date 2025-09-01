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
import Rozier from './Rozier'

import { defineLazyElement } from '~/utils/custom-elements/defineLazyElement'
import { toKebabCase } from '~/utils/string/toKebabCase'

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
    // Auto-register all custom elements under a path
    const modules = import.meta.glob('./custom-elements/*.{js,ts}')

    for (const path in modules) {
        const fileName = path.match(/\/([^/]+)\.(js|ts)$/)[1] // MyElement or my-element
        const tagName = toKebabCase(fileName)

        defineLazyElement(tagName, modules[path])
    }

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
