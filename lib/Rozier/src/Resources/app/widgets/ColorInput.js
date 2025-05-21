export default function () {
    // Query all `input[type="color"]` elements and wrap them in a container to enable swtiching from
    // `input[type="text"]` to `input[type="color"]` to allow for color picking but still allow for null value.
    /**
     * @var {NodeListOf<HTMLInputElement>}
     */
    const colorInputs = document.querySelectorAll('input[type="color"]');

    colorInputs.forEach((colorInput) => {
        // Prevent wrapping if already wrapped
        if (colorInput.parentElement && colorInput.parentElement.classList.contains('color-input-wrapper')) {
            return;
        }
        // Create wrapper
        const initialValue = colorInput.getAttribute('data-initial-value');
        const wrapper = document.createElement('div');
        wrapper.className = 'color-input-wrapper';
        colorInput.parentNode.insertBefore(wrapper, colorInput);
        wrapper.appendChild(colorInput);

        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.classList.add('color-toggle-btn');
        toggleBtn.classList.add('uk-button');
        toggleBtn.textContent = 'Reset';
        wrapper.appendChild(toggleBtn);

        // Initialize the color input to text type
        if (initialValue && initialValue !== '') {
            wrapper.classList.add('color-input-wrapper--defined');
            wrapper.style.setProperty('--color-input-value', initialValue);
        }
        toggleColorInput(colorInput, toggleBtn, initialValue);

        toggleBtn.addEventListener('click', () => {
            toggleColorInput(colorInput, toggleBtn);
        });
        // set a css variable on the wrapper to set the color on colorInput change event
        colorInput.addEventListener('change', () => {
            if (colorInput.value && colorInput.value !== '') {
                wrapper.style.setProperty('--color-input-value', colorInput.value);
                wrapper.classList.add('color-input-wrapper--defined');
            } else {
                wrapper.style.removeProperty('--color-input-value');
                wrapper.classList.remove('color-input-wrapper--defined');
            }
        });
    });
}

/**
 * @param {HTMLInputElement} input
 * @param {HTMLButtonElement} button
 * @param {string|undefined} initialValue
 */
function toggleColorInput(input, button, initialValue = undefined) {
    if (input.type === 'color') {
        input.type = 'text';
        button.textContent = input.getAttribute('data-color-picker-label');
    } else {
        input.type = 'color';
        input.value = initialValue || input.value || '#000000';
        button.textContent = input.getAttribute('data-hex-color-label');
    }
}
