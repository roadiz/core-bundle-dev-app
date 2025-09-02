/**
 * Fetch Nodes Source from search terms.
 *
 * @param  {String} searchTerms
 * @return Promise
 */
export function getNodesSourceFromSearch(searchTerms) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'searchNodesSources',
        searchTerms: searchTerms,
    }

    return fetch(window.RozierConfig.routes.searchAjax + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
            // Required to prevent using this route as referer when login again
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data.data !== 'undefined' && data.data.length > 0) {
                return data.data
            } else {
                return []
            }
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
        })
}
