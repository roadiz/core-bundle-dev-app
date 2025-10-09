/**
 * Fetch documents Folders.
 *
 * @return Promise
 */
export function getFolders() {
    const postData = {
        _token: window.RozierConfig.ajaxToken,
        _action: 'foldersExplorer',
    }

    return fetch(
        window.RozierConfig.routes.foldersAjaxExplorer +
            '?' +
            new URLSearchParams(postData),
        {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    )
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
