import type { Meta, StoryObj } from '@storybook/html-vite'
import { http, HttpResponse, delay } from 'msw'
import { rzEntityThumbnailRenderer } from '../app/utils/storybook/renderer/rzEntityThumbnail'
import '../app/custom-elements/RzEntityThumbnail'

const ENTITY_THUMBNAIL_ENDPOINT = '/rz-admin/ajax/entity-thumbnail'
const SIZES = ['small', 'medium', 'large'] as const

export type Args = {
    entityClass: string
    entityId: string
    size?: (typeof SIZES)[number]
}

const meta: Meta<Args> = {
    title: 'Components/EntityThumbnail',
    tags: ['autodocs'],
    args: {
        entityClass: 'RZ\\Roadiz\\CoreBundle\\Entity\\Document',
        entityId: '42',
        size: 'medium',
    },
    argTypes: {
        entityClass: {
            control: { type: 'text' },
            description: 'The fully qualified class name of the entity',
        },
        entityId: {
            control: { type: 'text' },
            description: 'The ID of the entity',
        },
        size: {
            options: SIZES,
            control: { type: 'radio' },
            description: 'The size of the thumbnail',
        },
    },
}

export default meta
type Story = StoryObj<Args>

/**
 * Default entity thumbnail component showing a medium-sized thumbnail.
 * Uses global MSW handler from preview.ts
 */
export const Default: Story = {
    render: rzEntityThumbnailRenderer,
}

/**
 * Small thumbnail size variant.
 */
export const Small: Story = {
    args: {
        size: 'small',
    },
    render: rzEntityThumbnailRenderer,
}

/**
 * Large thumbnail size variant.
 */
export const Large: Story = {
    args: {
        size: 'large',
    },
    render: rzEntityThumbnailRenderer,
}

/**
 * User entity thumbnail (uses email as ID).
 * Uses global MSW handler which detects User class.
 */
export const UserEntity: Story = {
    args: {
        entityClass: 'RZ\\Roadiz\\CoreBundle\\Entity\\User',
        entityId: 'test@test.test',
    },
    render: rzEntityThumbnailRenderer,
}

/**
 * Multiple thumbnails in different sizes side by side.
 */
export const MultipleSizes: Story = {
    render: (args) => {
        const container = document.createElement('div')
        container.style.display = 'flex'
        container.style.gap = '1rem'
        container.style.alignItems = 'flex-start'

        const sizes: Array<(typeof SIZES)[number]> = [
            'small',
            'medium',
            'large',
        ]

        sizes.forEach((size) => {
            const thumbnail = document.createElement('rz-entity-thumbnail')
            thumbnail.setAttribute('entity-class', args.entityClass)
            thumbnail.setAttribute('entity-id', args.entityId)
            thumbnail.setAttribute('size', size)
            container.appendChild(thumbnail)
        })

        return container
    },
}

/**
 * Loading state (shown before IntersectionObserver triggers).
 */
export const LoadingState: Story = {
    render: () => {
        const container = document.createElement('div')
        container.className =
            'rz-entity-thumbnail rz-entity-thumbnail--medium rz-entity-thumbnail--loading'
        container.innerHTML = '<div class="rz-entity-thumbnail__spinner"></div>'
        return container
    },
}

/**
 * Simulates a slow network request with delay.
 */
export const SlowLoading: Story = {
    parameters: {
        msw: {
            handlers: [
                http.get(ENTITY_THUMBNAIL_ENDPOINT, async () => {
                    await delay(2000) // 2 second delay
                    return HttpResponse.json({
                        url: 'https://images.unsplash.com/photo-1557821552-17105176677c?w=200&h=150&fit=crop',
                        alt: 'Sample document thumbnail',
                        title: 'Sample Document',
                        width: 200,
                        height: 150,
                    })
                }),
            ],
        },
    },
    render: rzEntityThumbnailRenderer,
}

/**
 * Mocked HTTP error response (404).
 */
export const MockedError: Story = {
    parameters: {
        msw: {
            handlers: [
                http.get(ENTITY_THUMBNAIL_ENDPOINT, async () => {
                    await delay(800)
                    return new HttpResponse(null, {
                        status: 404,
                        statusText: 'Not Found',
                    })
                }),
            ],
        },
    },
    render: rzEntityThumbnailRenderer,
}

/**
 * Mocked empty thumbnail response (entity exists but has no thumbnail).
 */
export const MockedEmpty: Story = {
    parameters: {
        msw: {
            handlers: [
                http.get(ENTITY_THUMBNAIL_ENDPOINT, () => {
                    return HttpResponse.json({
                        url: null,
                        alt: null,
                        title: 'Document without thumbnail',
                        width: null,
                        height: null,
                    })
                }),
            ],
        },
    },
    render: rzEntityThumbnailRenderer,
}

/**
 * Error state when entity cannot be loaded.
 */
export const ErrorState: Story = {
    render: () => {
        const container = document.createElement('div')
        container.className =
            'rz-entity-thumbnail rz-entity-thumbnail--medium rz-entity-thumbnail--error'
        container.setAttribute('title', 'HTTP error! status: 404')
        container.innerHTML =
            '<div class="rz-entity-thumbnail__placeholder">!</div>'
        return container
    },
}

/**
 * Empty state when entity has no thumbnail.
 */
export const EmptyState: Story = {
    render: () => {
        const container = document.createElement('div')
        container.className =
            'rz-entity-thumbnail rz-entity-thumbnail--medium rz-entity-thumbnail--empty'
        container.innerHTML =
            '<div class="rz-entity-thumbnail__placeholder"></div>'
        return container
    },
}
