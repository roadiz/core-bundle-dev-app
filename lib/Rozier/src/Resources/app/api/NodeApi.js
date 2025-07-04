import request from 'axios'

/**
 * Fetch Nodes from an array of node id.
 *
 * @param {Array} ids
 * @param {Object} filters
 * @returns {Promise<R>|Promise.<T>}
 */
export function getNodesByIds({ ids = [], filters = {} }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'nodesByIds',
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
        url: window.RozierRoot.routes.nodesAjaxByArray,
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
 * Fetch Nodes from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @returns {Promise.<T>|Promise<R>}
 */
export function getNodes({ searchTerms, preFilters, filters, filterExplorerSelection, moreData }) {
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

    if (filterExplorerSelection) {
        if (filterExplorerSelection.id) {
            postData.tagId = filterExplorerSelection.id
        }
    }

    if (preFilters && preFilters.nodeTypes) {
        postData.nodeTypes = JSON.parse(preFilters.nodeTypes)
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.nodesAjaxExplorer,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.nodes) {
                return {
                    items: response.data.nodes,
                    filters: response.data.filters,
                }
            } else {
                return {}
            }
        })
        .catch((error) => {
            throw new Error(error)
        })
}
