/**
 * Get a random image.
 *
 * @returns {Promise<R>|Promise.<T>}
 */
export function getImage() {
    return fetch(window.RozierConfig.routes.splashRequest, {
        method: 'GET',
        headers: {
            Accept: 'application/json',
            // Required to prevent using this route as referer when login again
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(async (response) => {
            return (await response.json()).url
        })
        .catch(() => {
            throw new Error('Image not found')
        })
}
