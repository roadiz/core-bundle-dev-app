/**
 * RzInputColor is a custom element that wraps a color input and a button to toggle between color and text input types.
 */
export default class RzInputColor extends HTMLElement {
  constructor() {
    super()

    /** @type {HTMLInputElement|null} */
    this.colorInput = null
    /** @type {HTMLButtonElement|null} */
    this.toggleBtn = null
    this.initialValue = null
    this.toggleColorInput = this.toggleColorInput.bind(this)
  }

  connectedCallback() {
    this.colorInput = this.querySelector('input[type="color"]')
    this.toggleBtn = this.querySelector('.color-toggle-btn')

    if (!this.toggleBtn) {
      console.warn('RzInputColor: Missing toggle button')
      return
    }
    if (!this.toggleBtn) {
      console.warn('RzInputColor: Missing color input')
      return
    }

    this.initialValue = this.getAttribute('data-initial-value')
    this.className = 'color-input-wrapper'

    // Initialize the color input to text type
    if (this.initialValue && this.initialValue !== '') {
      this.classList.add('color-input-wrapper--defined')
      this.style.setProperty('--color-input-value', this.initialValue)
    }
    this.toggleColorInput()

    this.toggleBtn.addEventListener('click', this.toggleColorInput)
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

  toggleColorInput() {
    if (!this.colorInput) {
      return
    }

    if (this.colorInput.type === 'color') {
      this.colorInput.type = 'text'
      this.toggleBtn.textContent = this.getAttribute('data-color-picker-label')
      return
    }

    this.colorInput.type = 'color'
    this.colorInput.value = this.colorInput.value || this.initialValue || '#000000'
    this.toggleBtn.textContent = this.getAttribute('data-hex-color-label')
  }
}
