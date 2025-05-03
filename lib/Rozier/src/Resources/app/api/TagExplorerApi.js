/**
 * Fetch tags.
 *
 * @return Promise
 */
export function getTags() {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'tagsExplorer',
    }

    return fetch(window.RozierRoot.routes.tagsAjaxExplorer + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
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
        _token: window.RozierRoot.ajaxToken,
        _action: 'tagsExplorer',
        onlyParents: true,
    }

    return fetch(window.RozierRoot.routes.tagsAjaxExplorer + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
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
