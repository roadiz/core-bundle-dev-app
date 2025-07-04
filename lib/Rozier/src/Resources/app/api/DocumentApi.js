import request from 'axios'

/**
 * Fetch Documents from an array of document id.
 *
 * @param {Array} ids
 * @param {Object} filters
 * @returns {Promise<R>|Promise.<T>}
 */
export function getDocumentsByIds({ ids = [], filters = {} }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'documentsByIds',
    }
    if (filters && filters._locale) {
        postData._locale = filters._locale
    }
    /*
     * We need to send the ids as an object with keys as string
     * when Varnish is enabled, the query string is sorted
     */
    for (let i = 0; i < ids.length; i++) {
        postData['ids[' + i + ']'] = ids[i]
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.documentsAjaxByArray,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.documents) {
                return {
                    items: response.data.documents,
                    trans: response.data.trans,
                }
            } else {
                return null
            }
        })
        .catch((error) => {
            // Log request error or display a message
            throw new Error(error.response.data.humanMessage)
        })
}

/**
 * Fetch Documents from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @return Promise
 */
export function getDocuments({ searchTerms, preFilters, filters, filterExplorerSelection, moreData }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'toggleExplorer',
        search: searchTerms,
        page: 1,
    }

    if (preFilters && preFilters._locale) {
        postData._locale = preFilters._locale
    }
    if (filters && filters._locale) {
        postData._locale = filters._locale
    }

    if (filterExplorerSelection && filterExplorerSelection.id) {
        postData.folderId = filterExplorerSelection.id
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.documentsAjaxExplorer,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.documents) {
                return {
                    items: response.data.documents,
                    filters: response.data.filters,
                    trans: response.data.trans,
                }
            } else {
                return {}
            }
        })
        .catch((error) => {
            // Log request error or display a message
            throw new Error(error)
        })
}

export function setDocument(formData) {
    return request.post(window.location.href, formData, {
        headers: { Accept: 'application/json' },
    })
}
