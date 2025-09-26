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

    this.interval = window.setInterval(async () => {
      try {
        const response = await fetch(window.RozierConfig.routes.ping, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            // Required to prevent using this route as referer when login again
            'X-Requested-With': 'XMLHttpRequest',
          },
        })
        if (!response.ok) {
          if (response.status === 401 || response.status === 403) {
            this.store.commit(LOGIN_CHECK_DISCONNECTED)
          }
          else {
            this.store.commit(HEALTH_CHECK_FAILED)
          }
        }
        else {
          const responseUrl = new URL(response.url)
          if (responseUrl.pathname !== window.RozierConfig.routes.ping) {
            // User has been redirected to login
            this.store.commit(LOGIN_CHECK_DISCONNECTED)
            return
          }
          if (response.status === 200 || response.status === 202) {
            this.store.commit(LOGIN_CHECK_CONNECTED)
            this.store.commit(HEALTH_CHECK_SUCCEEDED)
            this.check()
          }
        }
      }
      catch {
        this.store.commit(HEALTH_CHECK_FAILED)
      }
    }, this.intervalDuration)
  }
}
