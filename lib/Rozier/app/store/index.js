import Vue from 'vue'
import Vuex from 'vuex'
import { KEYBOARD_EVENT_ESCAPE } from '../types/mutationTypes'

// Modules
import explorer from './modules/ExplorerStoreModule'
import drawers from './modules/DrawersStoreModule'
import filterExplorer from './modules/FilterExplorerStoreModule'
import tags from './modules/TagsStoreModule'

Vue.use(Vuex)
export default new Vuex.Store({
    modules: {
        explorer,
        filterExplorer,
        drawers,
        tags,
    },
    state: {
        translations: window.RozierConfig.messages,
    },
    actions: {
        escape({ commit }) {
            commit(KEYBOARD_EVENT_ESCAPE)
        },
    },
})
