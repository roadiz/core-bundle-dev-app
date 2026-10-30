import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

export type RzPageOptions = {
    id?: string
    content?: HTMLElement | string
}

const COMPONENT_CLASS_NAME = 'rz-page'

/** Mirrors the <main is="rz-page"> of layouts/base.html.twig, without its legacy overlay, loader and UIkit hooks. */
export function rzPageRenderer(args: RzPageOptions) {
    const id = args.id || COMPONENT_CLASS_NAME
    const page = document.createElement('main', { is: COMPONENT_CLASS_NAME })
    page.setAttribute('is', COMPONENT_CLASS_NAME)
    page.id = id
    page.classList.add(COMPONENT_CLASS_NAME)

    const scrollable = document.createElement('div')
    scrollable.id = 'main-content-scrollable'
    if (args.content instanceof HTMLElement) {
        scrollable.appendChild(args.content)
    } else if (args.content) {
        scrollable.innerHTML = args.content
    }
    page.appendChild(scrollable)

    // Hidden by main.css, kept to match layouts/base.html.twig
    page.appendChild(
        rzButtonRenderer({
            iconClass: 'rz-icon-ri--arrow-up-line',
            size: 'xs',
            attributes: {
                class: 'rz-back-to-top-button',
                title: 'Back to top',
                command: '--scroll-top',
                commandfor: id,
            },
        }),
    )

    return page
}
