import request from 'axios'

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

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.tagsAjaxExplorer,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.tags) {
                return {
                    items: response.data.tags,
                }
            } else {
                return {}
            }
        })
        .catch((error) => {
            throw new Error(error)
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

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.tagsAjaxExplorer,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.tags) {
                return {
                    items: response.data.tags,
                }
            } else {
                return {}
            }
        })
        .catch((error) => {
            throw new Error(error)
        })
}
