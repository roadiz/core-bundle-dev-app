export type ImageSource = {
    type: string
    srcset: string
}

export type Image = {
    alt?: string
    src?: string
    width?: number
    height?: number
    sources?: ImageSource[]
}

export function rzSourceRenderer(source: ImageSource) {
    const node = document.createElement('source')
    node.setAttribute('type', source.type)
    node.setAttribute('srcset', source.srcset)
    return node
}

export function rzImageRenderer(media: Image) {
    const img = document.createElement('img')

    if (media.alt) img.setAttribute('alt', media.alt)
    if (media.src) img.setAttribute('src', media.src)
    if (media.width) img.setAttribute('width', media.width.toString())
    if (media.height) img.setAttribute('height', media.height.toString())

    if (media.sources?.length) {
        const picture = document.createElement('picture')
        media.sources.forEach((source) => {
            const sourceNode = rzSourceRenderer(source)
            picture.appendChild(sourceNode)
        })
        picture.appendChild(img)
        return picture
    }

    return img
}
