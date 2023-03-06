import {
    BLANCHETTE_EDITOR_INIT,
    BLANCHETTE_EDITOR_IS_LOADING,
    BLANCHETTE_EDITOR_LOADED,
    BLANCHETTE_EDITOR_ERROR,
    BLANCHETTE_EDITOR_SAVE_SUCCESS,
} from '../../types/mutationTypes'
import * as DocumentApi from '../../api/DocumentApi'
import * as Utils from '../../utils'

/**
 *  State
 */
const state = {
    isLoading: true,
    originalUrl: '',
    editor: null,
}

/**
 *  Actions
 */
const actions = {
    blanchetteEditorInit({ commit, dispatch }, { url, editor }) {
        dispatch('blanchetteEditorIsLoading')

        commit(BLANCHETTE_EDITOR_INIT, { url, editor })
    },
    blanchetteEditorIsLoading({ commit }) {
        commit(BLANCHETTE_EDITOR_IS_LOADING)
    },
    blanchetteEditorLoaded({ commit }) {
        commit(BLANCHETTE_EDITOR_LOADED)
    },
    blanchetteEditorSave({ commit, state }, { url, filename }) {
        const blob = Utils.dataURItoBlob(url)
        const form = state.editor.getElementsByTagName('form')[0]

        let formData = new FormData(form)
        formData.append('form[editDocument]', blob, filename)

        commit(BLANCHETTE_EDITOR_IS_LOADING)

        return DocumentApi.setDocument(formData)
            .then((res) => {
                commit(BLANCHETTE_EDITOR_LOADED)
                if (res.data && res.data.path) {
                    window.UIkit.notify({
                        message: res.data.message,
                        status: 'success',
                        timeout: 2000,
                        pos: 'top-center',
                    })

                    commit(BLANCHETTE_EDITOR_SAVE_SUCCESS, { path: res.data.path })
                } else {
                    throw new Error('No path found')
                }
            })
            .catch((error) => {
                console.error(error)
                commit(BLANCHETTE_EDITOR_ERROR, { error })
                commit(BLANCHETTE_EDITOR_LOADED)
            })
    },
}

/**
 *  Mutations
 */
const mutations = {
    [BLANCHETTE_EDITOR_INIT](state, { url, editor }) {
        state.originalUrl = url
        state.editor = editor
    },
    [BLANCHETTE_EDITOR_IS_LOADING](state) {
        state.isLoading = true
    },
    [BLANCHETTE_EDITOR_ERROR](state, { error }) {
        state.error = error.message
    },
    [BLANCHETTE_EDITOR_LOADED](state) {
        state.isLoading = false
    },
    [BLANCHETTE_EDITOR_SAVE_SUCCESS](state, { path }) {
        state.originalUrl = path
    },
}

export default {
    state,
    actions,
    mutations,
}
