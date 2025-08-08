/**
 * Fetch all Tags.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param filterExplorerSelection
 * @param {Boolean} moreData
 * @returns {Promise<R>|Promise.<T>}
 */
export function getTags({ searchTerms, preFilters, filters, filterExplorerSelection, moreData }) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'getTags',
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

    if (filterExplorerSelection && filterExplorerSelection.id) {
        postData.tagId = filterExplorerSelection.id
    }

    return fetch(window.RozierConfig.routes.tagsAjaxExplorerList + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
            // Required to prevent using this route as referer when login again
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.tags) {
                return {
                    items: data.tags,
                    filters: data.filters,
                }
            }

            throw new Error('No tags found')
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
        })
}

/**
 * Fetch Tags from an array of node id.
 *
 * @param {Array} ids
 * @returns {Promise<R>|Promise.<T>}
 */
export function getTagsByIds({ ids = [], filters = {} }) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'documentsByIds',
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

    return fetch(window.RozierConfig.routes.tagsAjaxByArray + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
            // Required to prevent using this route as referer when login again
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(async (response) => {
            const data = await response.json()
            if (typeof data !== 'undefined' && data.tags) {
                return {
                    items: data.tags,
                }
            } else {
                return {}
            }
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
        })
}

/**
 * Create a new tag.
 *
 * @param {String} tagName
 * @returns {Promise<R>|Promise.<T>}
 */
export function createTag({ tagName }) {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'documentsByIds',
        tagName: tagName,
    }

    return fetch(window.RozierConfig.routes.tagsAjaxCreate + '?' + new URLSearchParams(postData), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            // Required to prevent using this route as referer when login again
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(async (response) => {
            const data = await response.json()

            if (typeof data !== 'undefined' && data.tag) {
                return data.tag
            }

            throw new Error('Tag creation error')
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
        })
}
