import request from 'axios'
import qs from 'qs'

/**
 * Fetch Joins from an array of node id.
 *
 * @param {Array} ids
 * @param filters
 * @returns {Promise<R>|Promise.<T>}
 */
export function getItemsByIds({ ids = [], filters }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        providerClass: filters.providerClass,
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
        url: window.RozierRoot.routes.providerAjaxByArray,
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
            // Log request error or display a message
            throw new Error(error.response.data.humanMessage)
        })
}

/**
 * Fetch Joins from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @returns {Promise.<T>|Promise<R>}
 */
export function getItems({ searchTerms, preFilters, filters, filterExplorerSelection, moreData }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'toggleExplorer',
        providerClass: preFilters ? preFilters.providerClass : null,
        options: preFilters ? preFilters.providerOptions : null,
        search: searchTerms,
        page: 1,
    }
    if (preFilters && preFilters._locale) {
        postData._locale = preFilters._locale
    }
    if (filters && filters._locale) {
        postData._locale = filters._locale
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.providerAjaxExplorer + '?' + qs.stringify(postData), // need to use QS to compile array parameters
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.entities) {
                return {
                    items: response.data.entities,
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
