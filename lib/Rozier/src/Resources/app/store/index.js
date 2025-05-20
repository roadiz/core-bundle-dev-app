import Vuex from 'vuex'
import {
    KEYBOARD_EVENT_ESCAPE,
    LOGIN_CHECK_DISCONNECTED,
    HEALTH_CHECK_FAILED,
    LOGIN_CHECK_CONNECTED,
    HEALTH_CHECK_SUCCEEDED,
} from '../types/mutationTypes'

// Modules
import nodesSourceSearch from './modules/NodesSourceSearchStoreModule'
import explorer from './modules/ExplorerStoreModule'
import drawers from './modules/DrawersStoreModule'
import filterExplorer from './modules/FilterExplorerStoreModule'
import tags from './modules/TagsStoreModule'
import documentPreview from './modules/DocumentPreviewStoreModule'
import blanchetteEditor from './modules/BlanchetteEditorStoreModule'

export default new Vuex.Store({
    modules: {
        nodesSourceSearch,
        explorer,
        filterExplorer,
        drawers,
        tags,
        documentPreview,
        blanchetteEditor,
    },
    state: {
        translations: window.RozierConfig.messages,
        connected: true,
        healthChecked: true,
    },
    mutations: {
        [LOGIN_CHECK_CONNECTED](state) {
            state.connected = true
        },
        [LOGIN_CHECK_DISCONNECTED](state) {
            state.connected = false
        },
        [HEALTH_CHECK_SUCCEEDED](state) {
            state.healthChecked = true
        },
        [HEALTH_CHECK_FAILED](state) {
            state.healthChecked = false
        },
    },
    actions: {
        escape({ commit }) {
            commit(KEYBOARD_EVENT_ESCAPE)
        },
    },
})
