interface Thumbnail {
    alt?: string | null
    embedId?: string | null
    embedPlatform?: string | null
    hotspot?: string | null
    imageAverageColor?: string
    imageHeight?: number
    imageWidth?: number
    mediaDuration?: number
    mimeType?: string
    processable?: boolean
    relativePath?: string
    type?: string
    url?: string
}

export interface NodeSourceSearch {
    classname?: string
    color?: string
    displayable?: string
    editItem?: string
    id?: number
    published?: boolean
    thumbnail?: Thumbnail
}
