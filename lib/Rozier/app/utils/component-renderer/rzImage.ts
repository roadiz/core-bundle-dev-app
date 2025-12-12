import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export const COMPONENT_CLASS_NAME = 'rz-image'

type ImageSource = RzElement & {
    type: string
    srcset: string
}

export type RzImageData = RzElement & {
    alt?: string
    src?: string
    width?: number
    height?: number
    sources?: ImageSource[]
}

export function rzSourceRenderer(data: ImageSource) {
    const node = rzElement({
        tag: 'source',
        ...data,
    })
    node.setAttribute('type', data.type)
    node.setAttribute('srcset', data.srcset)

    return node
}

export function rzImageRenderer(data: RzImageData) {
    const root = rzElement({
        tag: 'img',
        ...data,
    })
    root.classList.add(COMPONENT_CLASS_NAME)

    if (data.alt) root.setAttribute('alt', data.alt)
    if (data.src) root.setAttribute('src', data.src)
    if (data.width) root.setAttribute('width', data.width.toString())
    if (data.height) root.setAttribute('height', data.height.toString())

    if (data.sources?.length) {
        const picture = document.createElement('picture')
        data.sources.forEach((source) => {
            const sourceNode = rzSourceRenderer(source)
            picture.appendChild(sourceNode)
        })
        picture.appendChild(root)
        return picture
    }

    return root
}
