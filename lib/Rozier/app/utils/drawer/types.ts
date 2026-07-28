export interface Hotspot {
    x: number
    y: number
}

export interface Document {
    url?: string
    alt?: string | null
    embedId?: string | null
    embedPlatform?: string | null
    hotspot?: Hotspot | null
    imageAverageColor?: string
    imageHeight?: number
    imageWidth?: number
    mediaDuration?: number
    mimeType?: string
    processable?: boolean
    relativePath?: string
    type?: string
}

export interface RzDrawerItem {
    classname?: string
    color?: string
    displayable?: string
    document?: number
    editImageHeight?: number
    editImageUrl?: string
    editImageWidth?: number
    editItem?: string
    embedPlatform?: string | null
    hasThumbnail?: boolean
    hotspot?: Hotspot
    icon?: string
    id?: number
    imageCropAlignment?: string
    isEmbed?: boolean
    isImage?: boolean
    isPdf?: boolean
    isPrivate?: boolean
    isSvg?: boolean
    isVideo?: boolean
    isWebp?: boolean
    originalHotspot?: Hotspot | null
    previewHtml?: string
    processable?: boolean
    published?: boolean
    relativePath?: string
    shortMimeType?: string
    shortType?: string
    thumbnail?: Document | null
    thumbnail80?: string
}

export interface DocumentItemAttribute {
    document?: number
    id?: number
    hotspot?: Hotspot | null
    imageCropAlignment?: string
}

export type ItemAttribute = number | DocumentItemAttribute
