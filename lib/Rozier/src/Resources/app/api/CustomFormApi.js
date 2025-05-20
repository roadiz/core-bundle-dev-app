/**
 * Fetch Joins from an array of node id.
 *
 * @param {Array} ids
 * @param filters
 * @returns {Promise<R>|Promise.<T>}
 */
export function getCustomFormsByIds({ ids = [], filters }) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
    }
    /*
     * We need to send the ids as an object with keys as string
     * when Varnish is enabled, the query string is sorted
     */
    for (let i = 0; i < ids.length; i++) {
        postData['ids[' + i + ']'] = ids[i]
    }

    return fetch(window.RozierConfig.routes.customFormsAjaxByArray + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            accept: 'application/json',
        },
    })
        .then(async (response) => {
            return {
                items: (await response.json()).forms,
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
 * @param {Object} filters
 * @param {Boolean} moreData
 * @returns {Promise.<T>|Promise<R>}
 */
export function getCustomForms({ searchTerms, filters, moreData }) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'toggleExplorer',
        search: searchTerms,
        page: 1,
    }

    if (moreData) {
        postData.page = filters ? filters.nextPage : 1
    }

    return fetch(window.RozierConfig.routes.customFormsAjaxExplorer + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            accept: 'application/json',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.customForms) {
                return {
                    items: data.customForms,
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
