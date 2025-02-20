import request from 'axios'

/**
 * Fetch NodeTypes from an array of node name.
 *
 * @param {Array} ids
 * @returns {Promise<R>|Promise.<T>}
 */
export function getNodeTypesByIds({ ids = [] }) {
    // Trim ids
    ids = ids.map((item) => item.trim())

    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'nodeTypesByIds',
    }
    /*
     * We need to send the ids as an object with keys as string
     * when Varnish is enabled, the query string is sorted
     */
    for (let i = 0; i < ids.length; i++) {
        postData['names[' + i + ']'] = ids[i]
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.nodeTypesAjaxByArray,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.items) {
                return {
                    items: response.data.items,
                }
            } else {
                return null
            }
        })
        .catch((error) => {
            throw new Error(error.response.data.humanMessage)
        })
}

/**
 * Fetch NodeTypes from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @returns {Promise.<T>|Promise<R>}
 */
export function getNodeTypes({ searchTerms, preFilters, filters, filterExplorerSelection, moreData }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'toggleExplorer',
        search: searchTerms,
        page: 1,
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.nodeTypesAjaxExplorer,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.nodeTypes) {
                return {
                    items: response.data.nodeTypes,
                    filters: response.data.filters,
                }
            } else {
                return {}
            }
        })
        .catch((error) => {
            // TODO
            // Log request error or display a message
            throw new Error(error)
        })
}
