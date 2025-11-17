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

import 'jquery-ui'
import 'jquery-ui/ui/widgets/autocomplete'

import GenericBulkActions from './behaviours/GenericBulkActions'
import Mobile from './behaviours/Mobile'
import Rozier from './Rozier'

import { defineLazyElement } from '~/utils/custom-element/defineLazyElement'
import customElementList from './custom-elements'
import '@ungap/custom-elements' // Polyfill for Safari (not implementing the customized built-in elements)

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
    // Auto-register custom elements
    for (const name in customElementList) {
        defineLazyElement(name, customElementList[name])
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
