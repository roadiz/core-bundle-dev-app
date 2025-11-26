import type { Preview } from '@storybook/html-vite'
import customTheme from './global-theme'
import { defineLazyElement } from '~/utils/custom-element/defineLazyElement'
import customElementList from '~/custom-elements'
import prettier from 'prettier/standalone'
import prettierPluginHtml from 'prettier/plugins/html'
import '@ungap/custom-elements' // Polyfill for Safari (not implementing the customized built-in elements)
import 'assets/css/main.css'

// Initialize preview environment
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
}

export default preview
