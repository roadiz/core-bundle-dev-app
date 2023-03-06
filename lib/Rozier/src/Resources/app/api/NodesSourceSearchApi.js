import request from 'axios'

/**
 * Fetch Nodes Source from search terms.
 *
 * @param  {String} searchTerms
 * @return Promise
 */
export function getNodesSourceFromSearch(searchTerms) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'searchNodesSources',
        searchTerms: searchTerms,
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.searchNodesSourcesAjax,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data.data !== 'undefined' && response.data.data.length > 0) {
                return response.data.data
            } else {
                return []
            }
        })
        .catch((error) => {
            throw new Error(error)
        })
}
