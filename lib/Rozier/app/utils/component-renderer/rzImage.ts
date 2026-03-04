import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-image'

type ImageSource = RzElement & {
    type: string
    srcset: string
}

export type RzImageOptions = RzElement & {
    alt?: string
    src?: string
    width?: number
    height?: number
    sources?: ImageSource[]
    loading?: 'eager' | 'lazy'
}

export function rzSourceRenderer(options: ImageSource) {
    const node = rzElement({
        tag: 'source',
        ...options,
    })
    node.setAttribute('type', options.type)
    node.setAttribute('srcset', options.srcset)

    return node
}

export function rzImageRenderer(options: RzImageOptions) {
    const root = rzElement({
        tag: 'img',
        ...options,
    })
    root.classList.add(COMPONENT_CLASS_NAME)

    if (options.alt) root.setAttribute('alt', options.alt)
    if (options.src) root.setAttribute('src', options.src)
    if (options.width) root.setAttribute('width', options.width.toString())
    if (options.height) root.setAttribute('height', options.height.toString())
    root.setAttribute('loading', options.loading || 'lazy')

    if (options.sources?.length) {
        const picture = document.createElement('picture')
        options.sources.forEach((source) => {
            const sourceNode = rzSourceRenderer(source)
            picture.appendChild(sourceNode)
        })
        picture.appendChild(root)
        return picture
    }

    return root
}
