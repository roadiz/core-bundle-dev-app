/**
 * Fetch Documents from an array of document id.
 *
 * @param {Array} ids
 * @returns {Promise<R>|Promise.<T>}
 */
export function getDocumentsByIds({ ids = [] }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'documentsByIds',
    }
    /*
     * We need to send the ids as an object with keys as string
     * when Varnish is enabled, the query string is sorted
     */
    for (let i = 0; i < ids.length; i++) {
        postData['ids[' + i + ']'] = ids[i]
    }

    return fetch(window.RozierRoot.routes.documentsAjaxByArray + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            accept: 'application/json',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.documents) {
                return {
                    items: data.documents,
                    trans: data.trans,
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
 * Fetch Documents from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @return Promise
 */
export function getDocuments({ searchTerms, filters, filterExplorerSelection, moreData }) {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'toggleExplorer',
        search: searchTerms,
        page: 1,
    }

    if (filterExplorerSelection && filterExplorerSelection.id) {
        postData.folderId = filterExplorerSelection.id
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return fetch(window.RozierRoot.routes.documentsAjaxExplorer + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            accept: 'application/json',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.documents) {
                return {
                    items: data.documents,
                    filters: data.filters,
                    trans: data.trans,
                }
            } else {
                return {}
            }
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
        })
}

export function setDocument(formData) {
    return fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: { Accept: 'application/json' },
    })
}
