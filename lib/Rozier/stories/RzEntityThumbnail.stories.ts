import type { Meta, StoryObj } from '@storybook/html-vite'
import { http, HttpResponse, delay } from 'msw'
import {
    type RzEntityThumbnailOptions,
    rzEntityThumbnailRenderer,
    SIZES,
} from '~/utils/storybook/renderer/rzEntityThumbnail'

const ENTITY_THUMBNAIL_ENDPOINT = '/rz-admin/ajax/entity-thumbnail'

type Args = RzEntityThumbnailOptions

const meta: Meta<Args> = {
    title: 'Components/Data display/EntityThumbnail',
    tags: ['autodocs'],
    render: (args) => rzEntityThumbnailRenderer(args),
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
export const Default: Story = {}

/**
 * Small thumbnail size variant.
 */
export const Small: Story = {
    args: {
        size: 'small',
    },
}

/**
 * Large thumbnail size variant.
 */
export const Large: Story = {
    args: {
        size: 'large',
    },
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

        SIZES.forEach((size) => {
            const thumbnail = document.createElement('rz-entity-thumbnail')
            thumbnail.setAttribute('entity-class', args.entityClass)
            thumbnail.setAttribute('entity-id', args.entityId)
            thumbnail.classList.add(`rz-entity-thumbnail--${size}`)
            container.appendChild(thumbnail)
        })

        return container
    },
    parameters: {
        controls: { exclude: ['size'] },
    },
}

/**
 * Loading state (shown before IntersectionObserver triggers).
 */
export const LoadingState: Story = {
    render: (args) => {
        const container = document.createElement('div')
        container.className = `rz-entity-thumbnail rz-entity-thumbnail--${args.size} rz-entity-thumbnail--loading`
        container.innerHTML = '<div class="rz-entity-thumbnail__spinner"></div>'
        return container
    },
    parameters: {
        controls: { include: ['size'] },
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
}

/**
 * Error state when entity cannot be loaded.
 */
export const ErrorState: Story = {
    render: (args) => {
        const container = document.createElement('div')
        container.className = `rz-entity-thumbnail rz-entity-thumbnail--${args.size} rz-entity-thumbnail--error`
        container.setAttribute('title', 'HTTP error! status: 404')
        container.innerHTML =
            '<div class="rz-entity-thumbnail__placeholder">!</div>'
        return container
    },
    parameters: {
        controls: { include: ['size'] },
    },
}

/**
 * Empty state when entity has no thumbnail.
 */
export const EmptyState: Story = {
    render: (args) => {
        const container = document.createElement('div')
        container.className = `rz-entity-thumbnail rz-entity-thumbnail--${args.size} rz-entity-thumbnail--empty`
        container.innerHTML =
            '<div class="rz-entity-thumbnail__placeholder"></div>'
        return container
    },
    parameters: {
        controls: { include: ['size'] },
    },
}
