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

    return fetch(window.RozierRoot.routes.foldersAjaxExplorer + '?' + new URLSearchParams(postData), {
        method: 'GET',
        headers: {
            Accept: 'application/json',
        },
    })
        .then(async (response) => {
            const data = await response.json()

            if (typeof data !== 'undefined' && data.folders) {
                return {
                    items: data.folders,
                }
            } else {
                return {}
            }
        })
        .catch(async (error) => {
            throw new Error((await error.response.json()).humanMessage)
        })
}
