/**
 * Fetch tags.
 *
 * @return Promise
 */
export function getTags() {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'tagsExplorer',
    }

    return fetch(window.RozierConfig.routes.tagsAjaxExplorer + '?' + new URLSearchParams(postData), {
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
 * Fetch tags.
 *
 * @return Promise
 */
export function getParentTags() {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'tagsExplorer',
        onlyParents: true,
    }

    return fetch(window.RozierConfig.routes.tagsAjaxExplorer + '?' + new URLSearchParams(postData), {
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
