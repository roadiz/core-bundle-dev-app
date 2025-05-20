/**
 * Fetch Joins from an array of node id.
 *
 * @param {Array} ids
 * @param filters
 * @returns {Promise<R>|Promise.<T>}
 */
export function getJoinsByIds({ ids = [], filters }) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        filters: filters,
        nodeTypeFieldName: filters.nodeTypeField,
        nodeTypeName: filters.nodeTypeName,
    }
    /*
     * We need to send the ids as an object with keys as string
     * when Varnish is enabled, the query string is sorted
     */
    for (let i = 0; i < ids.length; i++) {
        postData['ids[' + i + ']'] = ids[i]
    }

    return fetch(window.RozierConfig.routes.joinsAjaxByArray + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            accept: 'application/json',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.items) {
                return {
                    items: data.items,
                }
            } else {
                return null
            }
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
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
export function getJoins({ searchTerms, preFilters, filters, filterExplorerSelection, moreData }) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'toggleExplorer',
        nodeTypeName: preFilters.nodeTypeName,
        search: searchTerms,
        page: 1,
    }

    if (preFilters && preFilters.nodeTypeField) {
        postData.nodeTypeFieldName = preFilters.nodeTypeField
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return fetch(window.RozierConfig.routes.joinsAjaxExplorer + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            accept: 'application/json',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.entities) {
                return {
                    items: data.entities,
                    filters: data.filters,
                }
            } else {
                return {}
            }
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
        })
}
