import request from 'axios'

/**
 * Fetch documents Folders.
 *
 * @return Promise
 */
export function getFolders() {
    const postData = {
        _token: window.RozierRoot.ajaxToken,
        _action: 'foldersExplorer',
    }

    return request({
        method: 'GET',
        url: window.RozierRoot.routes.foldersAjaxExplorer,
        params: postData,
    })
        .then((response) => {
            if (typeof response.data !== 'undefined' && response.data.folders) {
                return {
                    items: response.data.folders,
                }
            } else {
                return {}
            }
        })
        .catch((error) => {
            // TODO
            // Log request error or display a message
            throw new Error(error)
        })
}
