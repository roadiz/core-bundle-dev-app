export default class RzInputColor extends HTMLElement {
  constructor() {
    super()

    /** @type {HTMLInputElement|null} */
    this.colorInput = null
    /** @type {HTMLButtonElement|null} */
    this.toggleBtn = null
    this.initializeColorInput = this.initializeColorInput.bind(this)
    this.toggleColorInput = this.toggleColorInput.bind(this)
  }

  connectedCallback() {
    this.colorInput = this.querySelector('input[type="color"]')
    this.toggleBtn = this.querySelector('button.color-toggle-btn')
    this.initializeColorInput()
  }

  initializeColorInput() {
    if (!this.colorInput || !this.toggleBtn) {
      return
    }

    const initialValue = this.getAttribute('data-initial-value')
    this.className = 'color-input-wrapper'

    // Initialize the color input to text type
    if (initialValue && initialValue !== '') {
      this.classList.add('color-input-wrapper--defined')
      this.style.setProperty('--color-input-value', initialValue)
    }
    this.toggleColorInput(initialValue)

    this.toggleBtn.addEventListener('click', () => {
      this.toggleColorInput()
    })
    // set a css variable on the wrapper to set the color on colorInput change event
    this.colorInput.addEventListener('change', () => {
      if (this.colorInput.value && this.colorInput.value !== '') {
        this.style.setProperty('--color-input-value', this.colorInput.value)
        this.classList.add('color-input-wrapper--defined')
      }
      else {
        this.style.removeProperty('--color-input-value')
        this.classList.remove('color-input-wrapper--defined')
      }
    })
  }

  /**
   * @param {string|undefined} initialValue
   */
  toggleColorInput(initialValue = undefined) {
    if (this.colorInput.type === 'color') {
      this.colorInput.type = 'text'
      this.toggleBtn.textContent = this.getAttribute('data-color-picker-label')
    }
    else {
      this.colorInput.type = 'color'
      this.colorInput.value = initialValue || this.colorInput.value || '#000000'
      this.toggleBtn.textContent = this.getAttribute('data-hex-color-label')
    }
  }
}
