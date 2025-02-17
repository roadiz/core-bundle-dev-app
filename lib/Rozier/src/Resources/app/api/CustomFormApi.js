import request from 'axios'

/**
 * Fetch Joins from an array of node id.
 *
 * @param {Array} ids
 * @param filters
 * @returns {Promise<R>|Promise.<T>}
 */
export function getCustomFormsByIds({ ids = [], filters }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
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
        url: window.RozierRoot.routes.customFormsAjaxByArray,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.forms) {
                return {
                    items: response.data.forms,
                }
            } else {
                return null
            }
        })
        .catch((error) => {
            // TODO
            // Log request error or display a message
            throw new Error(error.response.data.humanMessage)
        })
}

/**
 * Fetch Joins from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} filters
 * @param {Boolean} moreData
 * @returns {Promise.<T>|Promise<R>}
 */
export function getCustomForms({ searchTerms, filters, moreData }) {
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
        url: window.RozierRoot.routes.customFormsAjaxExplorer,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.customForms) {
                return {
                    items: response.data.customForms,
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
