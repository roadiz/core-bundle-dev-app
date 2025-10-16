import type { Preview } from '@storybook/html-vite'
import customTheme from './global-theme'

import './css/story-layout.css'

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
