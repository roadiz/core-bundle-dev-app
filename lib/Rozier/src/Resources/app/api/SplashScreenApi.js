/**
 * Get a random image.
 *
 * @returns {Promise<R>|Promise.<T>}
 */
export function getImage() {
    return fetch(window.RozierRoot.routes.splashRequest, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    })
        .then(async (response) => {
            return (await response.json()).url
        })
        .catch(() => {
            throw new Error('Image not found')
        })
}
