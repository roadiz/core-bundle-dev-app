import {
    DOCUMENT_PREVIEW_INIT,
    DOCUMENT_PREVIEW_DESTROY,
    DOCUMENT_PREVIEW_CLOSE,
    DOCUMENT_PREVIEW_OPEN,
    KEYBOARD_EVENT_SPACE,
    KEYBOARD_EVENT_ESCAPE,
} from '../../types/mutationTypes'

/**
 * State
 */
const state = {
    document: null,
    isVisible: false,
    isLoading: false,
}

const getters = {
    documentPreviewGetDocument: (state) => state.document,
}

/**
 * Actions
 */
const actions = {
    documentPreviewInit({ commit }, { document }) {
        commit(DOCUMENT_PREVIEW_INIT, { document })
    },
    documentPreviewDestroy({ commit }) {
        commit(DOCUMENT_PREVIEW_DESTROY)
    },
    documentPreviewClose({ commit }) {
        commit(DOCUMENT_PREVIEW_CLOSE)
    },
    documentPreviewOpen({ commit }) {
        commit(DOCUMENT_PREVIEW_OPEN)
    },
}

/**
 * Mutations
 */
const mutations = {
    [DOCUMENT_PREVIEW_INIT](state, { document }) {
        state.document = document
    },
    [DOCUMENT_PREVIEW_DESTROY](state) {
        if (!state.isVisible) {
            state.document = null
        }
    },
    [KEYBOARD_EVENT_SPACE](state) {
        if (state.document !== null && !state.isVisible) {
            state.isVisible = true
        } else if (state.isVisible) {
            state.isVisible = false
            state.document = null
        }
    },
    [KEYBOARD_EVENT_ESCAPE](state) {
        state.isVisible = false
        state.document = null
    },
    [DOCUMENT_PREVIEW_OPEN](state) {
        state.isVisible = true
    },
    [DOCUMENT_PREVIEW_CLOSE](state) {
        state.isVisible = false
        state.document = null
    },
}

export default {
    state,
    getters,
    actions,
    mutations,
}
