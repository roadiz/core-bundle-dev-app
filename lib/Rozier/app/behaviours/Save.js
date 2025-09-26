/*
 * You can add automatically form button to actions-menus
 * Just add them to the .rz-action-save class and use the data-action-save
 * attribute to point form ID to submit.
 */
export default class Save {
  constructor() {
    this.button = document.querySelector('.rz-action-save')
    this.actionMenu = document.querySelector('.actions-menu')
    this.formToSave = null

    if (!this.button || !this.actionMenu) {
      return
    }

    // Bind method
    this.onClick = this.onClick.bind(this)
    this.onKeyDown = this.onKeyDown.bind(this)

    // Unbind previous listeners if any
    this.button.removeEventListener('click', this.onClick)
    window.removeEventListener('keydown', this.onKeyDown)

    const formSelector = this.button.getAttribute('data-action-save')
    if (!formSelector) {
      return
    }
    this.formToSave = document.querySelector(formSelector)

    if (this.formToSave) {
      this.actionMenu.insertBefore(this.button, this.actionMenu.firstChild)
      this.button.addEventListener('click', this.onClick)
      window.addEventListener('keydown', this.onKeyDown)
    }
  }

  onKeyDown(event) {
    if (this.formToSave && (event.metaKey || event.ctrlKey) && event.key === 's') {
      event.preventDefault()
      this.formToSave.requestSubmit()
      return false
    }
  }

  onClick() {
    this.formToSave.requestSubmit()
  }
}
