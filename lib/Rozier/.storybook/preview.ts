import type { Preview } from '@storybook/html-vite'
import customTheme from './global-theme'
import { defineLazyElement } from '~/utils/custom-element/defineLazyElement'
import customElementList from '~/custom-elements'
import prettier from 'prettier/standalone'
import prettierPluginHtml from 'prettier/plugins/html'
import { initialize, mswLoader } from 'msw-storybook-addon'
import { http, HttpResponse } from 'msw'
import '@ungap/custom-elements' // Polyfill for Safari (not implementing the customized built-in elements)
import 'assets/css/main.css'

// Entity thumbnail endpoint and mock data
const ENTITY_THUMBNAIL_ENDPOINT = '/rz-admin/ajax/entity-thumbnail'

// Sample images for different entity types
const mockThumbnails: Record<string, object> = {
    document: {
        url: 'https://images.unsplash.com/photo-1557821552-17105176677c?w=200&h=150&fit=crop',
        alt: 'Sample document thumbnail',
        title: 'Sample Document',
        width: 200,
        height: 150,
    },
    user: {
        url: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&h=150&fit=crop',
        alt: 'User avatar',
        title: 'Test User',
        width: 150,
        height: 150,
    },
}

// Global MSW handlers
const globalHandlers = [
    http.get(ENTITY_THUMBNAIL_ENDPOINT, ({ request }) => {
        const url = new URL(request.url)
        const entityClass = url.searchParams.get('class') || ''

        // Return user thumbnail for User entities
        if (entityClass.includes('User')) {
            return HttpResponse.json(mockThumbnails.user)
        }

        // Default to document thumbnail
        return HttpResponse.json(mockThumbnails.document)
    }),
]

// Initialize MSW
initialize()
;(function () {
    // Auto-register custom elements
    for (const name in customElementList) {
        defineLazyElement(name, customElementList[name])
    }
})()

const preview: Preview = {
    parameters: {
        ...customTheme.parameters,
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/i,
            },
        },
        a11y: {
            // 'todo' - show a11y violations in the test UI only
            // 'error' - fail CI on a11y violations
            // 'off' - skip a11y checks entirely
            test: 'todo',
        },
        msw: {
            handlers: globalHandlers,
        },
        docs: {
            source: {
                transform: async (source) => {
                    try {
                        return prettier.format(source, {
                            parser: 'html',
                            plugins: [prettierPluginHtml],
                        })
                    } catch (error) {
                        console.warn('Prettier formatting failed:', error)
                        return source
                    }
                },
            },
        },
    },
    globalTypes: {
        ...customTheme.globalTypes,
    },
    initialGlobals: {
        ...customTheme.initialGlobals,
    },
    decorators: [...customTheme.decorators],
    loaders: [mswLoader], // Enable MSW loader for all stories
}

export default preview
