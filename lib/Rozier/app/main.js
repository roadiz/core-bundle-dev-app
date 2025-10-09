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

import GenericBulkActions from './behaviours/GenericBulkActions'
import Mobile from './behaviours/Mobile'
import Rozier from './Rozier'
import RzSaveButton from './custom-elements/RzSaveButton'

import { defineLazyElement } from '~/utils/custom-element/defineLazyElement'
import customElementList from './custom-elements'

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
    // TODO: Need to add a polyfill for Safari - https://www.sobyte.net/post/2021-08/safari-buildin-custom-element-polyfill/
    // Register all customized built-in elements with extends directive
    customElements.define('rz-save-button', RzSaveButton, { extends: 'button' })

    // Auto-register all anonymous custom elements
    for (const tagName in customElementList) {
        defineLazyElement(tagName, customElementList[tagName])
    }

    /*
     * init generic bulk actions widget
     */
    GenericBulkActions()
    new Mobile()

    window.addEventListener('pageshowend', () => {
        GenericBulkActions()
    })
})()
