/**
 * Fetch Nodes from an array of node id.
 *
 * @param {Array} ids
 * @returns {Promise<R>|Promise.<T>}
 */
export function getNodesByIds({ ids = [] }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'nodesByIds',
    }
    /*
     * We need to send the ids as an object with keys as string
     * when Varnish is enabled, the query string is sorted
     */
    for (let i = 0; i < ids.length; i++) {
        postData['ids[' + i + ']'] = ids[i]
    }

    return fetch(window.RozierRoot.routes.nodesAjaxByArray + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
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

    if (filterExplorerSelection && filterExplorerSelection.id) {
        postData.tagId = filterExplorerSelection.id
    }

    if (preFilters && preFilters.nodeTypes) {
        const nodeTypes = JSON.parse(preFilters.nodeTypes)
        if (Array.isArray(nodeTypes) && nodeTypes.length > 0) {
            postData.nodeTypes = nodeTypes
        }
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return fetch( window.RozierRoot.routes.nodesAjaxExplorer + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.nodes) {
                return {
                    items: data.nodes,
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
