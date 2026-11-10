import Rozier from './Rozier'

import { defineLazyElement } from '~/utils/custom-element/defineLazyElement'
import customElementList from './custom-elements'
import '@ungap/custom-elements' // Polyfill for Safari (not implementing the customized built-in elements)
import 'invokers-polyfill' // Polyfill for Invoker command API (fixing Safari support)

// Shared by the rz-code-editor and rz-markdown-editor CodeMirror instances
import 'assets/css/vendor/codemirror.css'
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
})()
