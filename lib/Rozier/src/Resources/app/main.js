import './less/vendor.less'
import './less/style.less'

import './vendor/heartcode-canvasloader-min.js'
import '../../node_modules/jquery.actual/jquery.actual'
import './vendor/jquery.tag-editor'
import './vendor/jquery.collection'
import '../../node_modules/bootstrap-switch/dist/js/bootstrap-switch'
import '../../node_modules/mousetrap/mousetrap'
import '../../node_modules/jquery.caret/dist/jquery.caret.js'
import '../../node_modules/jquery-minicolors/jquery.minicolors.js'

import UIkit from '../../node_modules/uikit/dist/js/uikit'
import '../../node_modules/uikit/dist/js/components/nestable'
import '../../node_modules/uikit/dist/js/components/sortable.js'
import '../../node_modules/uikit/dist/js/components/datepicker.js'
import '../../node_modules/uikit/dist/js/components/pagination.js'
import '../../node_modules/uikit/dist/js/components/notify.js'
import '../../node_modules/uikit/dist/js/components/tooltip.js'

import CodeMirror from 'codemirror'
import 'codemirror/mode/markdown/markdown.js'
import 'codemirror/mode/javascript/javascript.js'
import 'codemirror/mode/css/css.js'
import 'codemirror/addon/mode/overlay.js'
import 'codemirror/mode/xml/xml.js'
import 'codemirror/mode/yaml/yaml.js'
import 'codemirror/mode/gfm/gfm.js'
import 'codemirror/addon/display/rulers.js'

import 'jquery-ui'
import 'jquery-ui/ui/widgets/autocomplete'

import $ from 'jquery'
import Rozier from './Rozier'

window.CodeMirror = CodeMirror
window.UIkit = UIkit
/*
 * ============================================================================
 * Rozier entry point
 * ============================================================================
 */

window.Rozier = new Rozier()

/*
 * ============================================================================
 * Plug into jQuery standard events
 * ============================================================================
 */
$(document).ready(() => {
    window.Rozier.onDocumentReady()
})
