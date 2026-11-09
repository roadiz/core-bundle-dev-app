import {
    type RzMessageOptions,
    rzMessageRenderer,
} from '~/utils/storybook/renderer/rzMessage'

export type RzPagePreviewOptions = {
    title: string
    description?: string // HTML (block page_description)
    note?: string // secondary line under the description (block page_note)
    messages?: RzMessageOptions[] // session flashes
    content?: HTMLElement | string // block content
}

const COMPONENT_CLASS_NAME = 'rz-page-preview'

/** Mirrors the <body class="rz-page-preview"> of ui/layouts/page_preview.html.twig. */
export function rzPagePreviewRenderer(args: RzPagePreviewOptions) {
    const page = document.createElement('div')
    page.classList.add(COMPONENT_CLASS_NAME)

    const main = document.createElement('main')
    main.classList.add(`${COMPONENT_CLASS_NAME}__content`)
    page.appendChild(main)

    const header = document.createElement('header')
    header.classList.add(`${COMPONENT_CLASS_NAME}__header`)
    main.appendChild(header)

    const title = document.createElement('h1')
    title.classList.add(`${COMPONENT_CLASS_NAME}__title`, 'text-h1-md')
    title.textContent = args.title
    header.appendChild(title)

    if (args.description) {
        const description = document.createElement('div')
        description.classList.add(
            `${COMPONENT_CLASS_NAME}__description`,
            'text-body-md',
        )
        description.innerHTML = args.description
        header.appendChild(description)
    }

    if (args.note) {
        const note = document.createElement('p')
        note.classList.add(`${COMPONENT_CLASS_NAME}__note`, 'text-body-sm')
        note.textContent = args.note
        header.appendChild(note)
    }

    args.messages?.forEach((message) => {
        main.appendChild(rzMessageRenderer(message))
    })

    if (args.content instanceof HTMLElement) {
        main.appendChild(args.content)
    } else if (args.content) {
        main.insertAdjacentHTML('beforeend', args.content)
    }

    return page
}
