import { type RzDrawerItem } from '~/utils/drawer/types'
import { RzButtonOptions } from '~/utils/component-renderer/rzButton'
import {
    RzCardOptions,
    rzCardRenderer,
} from '~/utils/component-renderer/rzCard'

export function createDrawerItemElement({
    item,
    index,
    name,
    acceptEntity,
    onRemoveClick,
    onEditClick,
}: {
    item: RzDrawerItem
    index: number
    name: string
    acceptEntity: string
    onRemoveClick: (item: RzDrawerItem) => void
    onEditClick: (item: RzDrawerItem) => void
}): HTMLElement {
    const isDocument =
        item.isPdf || item.isImage || item.isVideo || item.isEmbed

    // Action buttons
    const buttons: RzButtonOptions[] = [
        {
            iconClass: 'rz-icon-ri--delete-bin-7-line',
            emphasis: 'tertiary',
            color: 'danger',
            attributes: {
                type: 'button', // do not submit form
            },
            on: {
                click: () => {
                    onRemoveClick(item)
                },
            },
        },
    ]

    // Edit link
    if (item.editItem) {
        const href = item.editItem + '?referer=' + window.location.pathname

        // Image
        if (item.isImage && !item.isEmbed && !item.isVideo && !item.isPdf) {
            buttons.unshift({
                tag: 'a',
                iconClass: 'rz-icon-ri--equalizer-3-line',
                emphasis: 'primary',
                attributes: {
                    href,
                },
                on: {
                    click: (event: MouseEvent) => {
                        event.preventDefault()
                        event.stopImmediatePropagation()
                        onEditClick(item)
                    },
                },
            })
        }
        // Other reference
        else {
            buttons.unshift({
                tag: 'a',
                iconClass: 'rz-icon-ri--edit-line',
                emphasis: 'primary',
                attributes: {
                    href,
                },
            })
        }
    }

    const cardOptions: RzCardOptions = {
        tag: 'li',
        buttonGroup: {
            buttons,
        },
    }

    // Previewable image
    if (item.isImage) {
        cardOptions.buttonGroupTop = {
            gap: 'sm',
            size: 'sm',
            buttons: [
                {
                    iconClass: 'rz-icon-ri--zoom-in-line',
                    emphasis: 'primary',
                    attributes: {
                        type: 'button', // do not submit form
                    },
                    on: {
                        click: () => {
                            document.dispatchEvent(
                                new CustomEvent('show-preview', {
                                    detail: { document: item },
                                }),
                            )
                        },
                    },
                },
            ],
        }
    }

    let iconClass = ''

    // Private item
    if (item.isPrivate) {
        iconClass = 'rz-icon-ri--lock-2-line'
    } else {
        // Image thumbnail
        if ((item.isImage && item.thumbnail80) || item.thumbnail?.url) {
            cardOptions.image = {
                src: item.thumbnail80 || item.thumbnail.url || '',
            }
        }

        // PDF icon
        if (item.isPdf) {
            iconClass = 'rz-icon-ri--file-pdf-2-line'
        } else if (item.isEmbed) {
            if (item.embedPlatform === 'vimeo') {
                iconClass = 'rz-icon-ri--vimeo-fill'
            } else if (item.embedPlatform === 'youtube') {
                iconClass = 'rz-icon-ri--youtube-fill'
            }
        } else if (item.isVideo) {
            iconClass = 'rz-icon-ri--file-video-fill'
        }
    }

    if (iconClass) {
        cardOptions.badge = {
            iconClass,
            size: 'md',
        }
    }

    // Title and overtitle (only if not a reference to a document)
    if (acceptEntity !== 'document') {
        cardOptions.overtitle = item.classname
        cardOptions.title = item.displayable
    }

    // Create card element
    const element = rzCardRenderer(cardOptions)

    element.dataset.id = item.id ? item.id.toString() : ''
    element.dataset.inputBaseName = `${name}[${index}]`

    // Main hidden input for form submission
    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = `${name}[${index}]${isDocument ? '[document]' : ''}`
    input.value = item.id.toString()
    element.appendChild(input)

    // Document hidden inputs for images
    if (item.isImage) {
        // Original hotspot
        const hotspotInput = document.createElement('input')
        hotspotInput.type = 'hidden'
        hotspotInput.name = `${name}[${index}][hotspot]`
        hotspotInput.value = item.hotspot
            ? JSON.stringify(item.hotspot)
            : 'null'
        element.appendChild(hotspotInput)

        // Image crop alignment
        const alignmentInput = document.createElement('input')
        alignmentInput.type = 'hidden'
        alignmentInput.name = `${name}[${index}][imageCropAlignment]`
        alignmentInput.value = item.imageCropAlignment || ''
        element.appendChild(alignmentInput)
    }

    return element
}
