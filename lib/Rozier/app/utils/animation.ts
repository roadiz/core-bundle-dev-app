import { sleep } from './sleep'

export async function fadeIn(
    element: HTMLElement,
    duration = 400,
): Promise<void> {
    return new Promise<void>((resolve) => {
        element.style.opacity = '0'
        element.style.display = 'block'
        element.style.transition = `opacity ${duration}ms ease-in`

        // Force le reflow
        void element.offsetWidth

        requestAnimationFrame(async () => {
            element.style.opacity = '1'
            await sleep(duration)
            resolve()
        })
    })
}

export async function fadeOut(
    element: HTMLElement,
    duration = 600,
): Promise<void> {
    return new Promise<void>((resolve) => {
        element.style.opacity = '1'
        element.style.transition = `opacity ${duration}ms ease-out`

        void element.offsetWidth

        requestAnimationFrame(async () => {
            element.style.opacity = '0'
            await sleep(duration)
            element.style.display = 'none'
            resolve()
        })
    })
}

// TODO: Refactor this function for do a beautiful slideUp
export async function slideUp(
    element: HTMLElement,
    duration = 300,
): Promise<void> {
    // TODO: Add stop function in way to stop previous animation
    return new Promise<void>((resolve) => {
        element.style.opacity = '1'
        element.style.transition = `opacity ${duration}ms ease-out`

        void element.offsetWidth

        requestAnimationFrame(async () => {
            element.style.opacity = '0'
            await sleep(duration)
            element.style.display = 'none'
            resolve()
        })
    })
}

// TODO: Refactor this function for do a beautiful slideDown
export async function slideDown(
    element: HTMLElement,
    duration = 300,
): Promise<void> {
    // TODO: Add stop function in way to stop previous animation
    return new Promise<void>((resolve) => {
        element.style.opacity = '0'
        element.style.display = 'block'
        element.style.transition = `opacity ${duration}ms ease-in`

        // Force le reflow
        void element.offsetWidth

        requestAnimationFrame(async () => {
            element.style.opacity = '1'
            await sleep(duration)
            resolve()
        })
    })
}
