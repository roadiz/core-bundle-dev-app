import { get, HttpError, routeUrl } from '~/utils/http'

const SESSION_EXPIRED_DIALOG_ID = 'rz-session-expired-dialog'
const HEALTH_CHECK_DIALOG_ID = 'rz-health-check-dialog'
const DIALOG_IDS = [SESSION_EXPIRED_DIALOG_ID, HEALTH_CHECK_DIALOG_ID]

/**
 * Login Check Event Service.
 *
 * Pings the back-end and opens the matching blocking dialog
 * (see ui/layouts/base.html.twig) when the session expired
 * or when the back-end stopped answering.
 */
export default class LoginCheckService {
    constructor() {
        this.intervalDuration = 10000
        this.openDialogId = null
        this.dismissedDialogIds = new Set()

        DIALOG_IDS.forEach((id) => {
            const dialog = this.getDialog(id)
            if (!dialog) return

            // The dialog close event is queued as a task: comparing with the dialog
            // we expect to be open is the only reliable way to tell a user dismissal
            // from a close() we triggered ourselves. A dialog without close button
            // can only be force-closed by the browser: let the next ping reopen it.
            const closeable = !!dialog.querySelector('[closetarget]')
            dialog.addEventListener('close', () => {
                if (closeable && this.openDialogId === id) {
                    this.dismissedDialogIds.add(id)
                }
            })

            // Fallback for browsers without closedby="none" support: these dialogs
            // must only be dismissed through their own close button, if they have one.
            dialog.addEventListener('cancel', (event) => event.preventDefault())
        })

        this.check()
    }

    /**
     * @param {string} id
     * @returns {HTMLDialogElement|null}
     */
    getDialog(id) {
        return document.getElementById(id)
    }

    /**
     * Dialogs are mutually exclusive. An expired session outranks a health check
     * failure: its dialog stays until a ping succeeds, as the user must log in again.
     *
     * @param {string} id
     */
    showDialog(id) {
        if (
            id === HEALTH_CHECK_DIALOG_ID &&
            this.openDialogId === SESSION_EXPIRED_DIALOG_ID
        ) {
            return
        }

        if (this.openDialogId !== id) this.closeOpenDialog()
        this.openDialogId = id

        const dialog = this.getDialog(id)
        // Do not nag the user with a dialog they already dismissed,
        // and never call showModal() on an already open dialog: it throws.
        if (!dialog || dialog.open || this.dismissedDialogIds.has(id)) return

        dialog.showModal()
    }

    /**
     * Only closes what this service opened, so a dialog opened by hand
     * from the console is not swept away by the next successful ping.
     */
    closeOpenDialog() {
        const dialog = this.openDialogId && this.getDialog(this.openDialogId)
        this.openDialogId = null

        if (dialog?.open) dialog.close()
    }

    closeDialogs() {
        this.closeOpenDialog()
        this.dismissedDialogIds.clear()
    }

    check() {
        if (this.interval) {
            window.clearInterval(this.interval)
        }

        this.interval = window.setInterval(async () => {
            try {
                await get('ping')
                this.closeDialogs()
                this.check()
            } catch (error) {
                if (!(error instanceof HttpError) || error.status === 0) {
                    this.showDialog(HEALTH_CHECK_DIALOG_ID)
                } else if (
                    error.status === 401 ||
                    error.status === 403 ||
                    // A 2xx that is not JSON: redirected to the login page
                    (error.status < 300 &&
                        new URL(error.url, window.location.href).pathname !==
                            routeUrl('ping'))
                ) {
                    this.showDialog(SESSION_EXPIRED_DIALOG_ID)
                } else {
                    this.showDialog(HEALTH_CHECK_DIALOG_ID)
                }
            }
        }, this.intervalDuration)
    }
}
