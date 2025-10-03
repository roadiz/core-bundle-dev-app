import { toType } from '../utils/plugins'

export default class TagEditPage extends HTMLElement {
  constructor() {
    super()

    this.form = this.querySelector('#edit-tag-form')
    this.formRow = this.querySelectorAll('.uk-form-row')
    this.dropdown = null

    // Binded methods
    this.onFormSubmit = this.onFormSubmit.bind(this)
  }

  connectedCallback() {
    // Methods
    if (this.form) {
      this.form.addEventListener('submit', this.onFormSubmit)
    }
  }

  disconnectedCallback() {
    if (this.form) {
      this.form.removeEventListener('submit', this.onFormSubmit)
    }
  }

  async onFormSubmit(event) {
    event.preventDefault()
    window.dispatchEvent(new CustomEvent('requestLoaderShow'))

    /*
         * Trigger event on window to notify open
         * widgets to close.
         */
    let pageChangeEvent = new CustomEvent('pagechange')
    window.dispatchEvent(pageChangeEvent)

    const formData = new FormData(this.form)
    const response = await fetch(window.location.href, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        // Required to prevent using this route as referer when login again
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    })
    if (!response.ok) {
      const data = await response.json()
      if (data.errors) {
        this.displayErrors(data.errors)
        window.dispatchEvent(
          new CustomEvent('pushToast', {
            detail: {
              message: data.message,
              status: 'danger',
            },
          }),
        )
      }
    }
    else {
      this.cleanErrors()
    }
    await window.Rozier.refreshMainTagTree()
    await window.Rozier.getMessages()
    window.dispatchEvent(new CustomEvent('requestLoaderHide'))

    return false
  }

  cleanErrors() {
    const previousErrors = document.querySelectorAll('.form-errored')
    previousErrors.forEach((index) => {
      index.classList.remove('form-errored')
      index.querySelector('.error-message').remove()
    })
  }

  /*
     * @param {Array} errors
     * @param {Boolean} keepExisting Keep existing errors.
     */
  displayErrors(errors, keepExisting) {
    // First clean fields
    if (!keepExisting || keepExisting === false) {
      this.cleanErrors()
    }

    for (let key in errors) {
      let classKey = null
      let errorMessage = null
      if (toType(errors[key]) === 'object') {
        this.displayErrors(errors[key], true)
      }
      else {
        classKey = key.replace('_', '-')
        if (errors[key] instanceof Array) {
          errorMessage = errors[key][0]
        }
        else {
          errorMessage = errors[key]
        }
        let field = document.querySelector('.form-col-' + classKey)
        if (field) {
          field.classList.add('form-errored')
          field.insertAdjacentHTML(
            'beforeend',
            '<p class="error-message uk-alert uk-alert-danger"><i class="uk-icon uk-icon-warning"></i> '
            + errorMessage
            + '</p>',
          )
        }
      }
    }
  }
}
