/**
 * @param {HTMLElement} element
 * @return {Promise<void>}
 */
export async function fadeIn(element, duration = 400) {
    return new Promise((resolve) => {
        element.style.opacity = 0;
        element.style.display = 'block';
        element.style.transition = `opacity ${duration}ms ease-in`;

        // Force le reflow
        void element.offsetWidth;

        requestAnimationFrame(() => {
            element.style.opacity = 1;
            setTimeout(resolve, duration);
        });
    });
}

/**
 * @param {HTMLElement} element
 * @return {Promise<void>}
 */
export async function fadeOut(element, duration = 600) {
    return new Promise((resolve) => {
        element.style.opacity = 1;
        element.style.transition = `opacity ${duration}ms ease-out`;

        void element.offsetWidth;

        requestAnimationFrame(() => {
            element.style.opacity = 0;
            setTimeout(() => {
                element.style.display = 'none';
                resolve();
            }, duration);
        });
    });
}

// TODO: Refactor this function for do a beautiful slideUp
/**
 * @param {HTMLElement} element
 * @return {Promise<void>}
 */
export async function slideUp(element, duration = 300) {
    // TODO: Add stop function in way to stop previous animation
    return new Promise((resolve) => {
        element.style.opacity = 1;
        element.style.transition = `opacity ${duration}ms ease-out`;

        void element.offsetWidth;

        requestAnimationFrame(() => {
            element.style.opacity = 0;
            setTimeout(() => {
                element.style.display = 'none';
                resolve();
            }, duration);
        });
    });
}

// TODO: Refactor this function for do a beautiful slideDown
/**
 * @param {HTMLElement} element
 * @return {Promise<void>}
 */
export async function slideDown(element, duration = 300) {
    // TODO: Add stop function in way to stop previous animation
    return new Promise((resolve) => {
        element.style.opacity = 0;
        element.style.display = 'block';
        element.style.transition = `opacity ${duration}ms ease-in`;

        // Force le reflow
        void element.offsetWidth;

        requestAnimationFrame(() => {
            element.style.opacity = 1;
            setTimeout(resolve, duration);
        });
    });
}
