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
    documentPreviewOpen({ commit }, { document }) {
        commit(DOCUMENT_PREVIEW_OPEN, { document })
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
        state.isVisible = false
        state.document = null
    },
    [KEYBOARD_EVENT_SPACE](state) {
        if (state.document && !state.isVisible) {
            // Display document preview
            state.isVisible = true
        } else {
            state.isVisible = false
            state.document = null
        }
    },
    [KEYBOARD_EVENT_ESCAPE](state) {
        state.isVisible = false
        state.document = null
    },
    [DOCUMENT_PREVIEW_OPEN](state, { document }) {
        if (document) {
            state.document = document
        }
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
