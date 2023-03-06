import request from 'axios'

/**
 * Get a random image.
 *
 * @returns {Promise<R>|Promise.<T>}
 */
export function getImage() {
    return request({
        method: 'GET',
        url: window.RozierRoot.routes.splashRequest,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        params: {
            _: Math.random(),
        },
    })
        .then((response) => {
            return response.data.url
        })
        .catch(() => {
            throw new Error('Image not found')
        })
}
