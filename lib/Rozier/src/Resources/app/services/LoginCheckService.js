import request from 'axios'
import {
    HEALTH_CHECK_FAILED,
    HEALTH_CHECK_SUCCEEDED,
    LOGIN_CHECK_CONNECTED,
    LOGIN_CHECK_DISCONNECTED,
} from '../types/mutationTypes'

/**
 * Login Check Event Service.
 *
 * @type {Object} store
 */
export default class LoginCheckService {
    constructor(store) {
        this.store = store
        this.intervalDuration = 10000
        this.check()
    }

    check() {
        if (this.interval) {
            window.clearInterval(this.interval)
        }

        this.interval = window.setInterval(() => {
            request({
                method: 'GET',
                url: window.RozierRoot.routes.ping,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                maxRedirects: 0,
            })
                .then((response) => {
                    if (response) {
                        if (new URL(response.request.responseURL).pathname !== window.RozierRoot.routes.ping) {
                            // User is redirected to login
                            this.store.commit(LOGIN_CHECK_DISCONNECTED)
                            return
                        }
                        if (response.status === 200 || response.status === 202) {
                            this.store.commit(LOGIN_CHECK_CONNECTED)
                            this.store.commit(HEALTH_CHECK_SUCCEEDED)
                            this.check()
                        }
                    }
                })
                .catch((error) => {
                    if (error.response.status === 401 || error.response.status === 403) {
                        this.store.commit(LOGIN_CHECK_DISCONNECTED)
                    } else {
                        this.store.commit(HEALTH_CHECK_FAILED)
                    }
                })
        }, this.intervalDuration)
    }
}
