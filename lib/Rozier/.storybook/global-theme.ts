import type { Preview } from '@storybook/html-vite'

import './css/theme.css'

export default {
    parameters: {
        backgrounds: false,
    },
    globalTypes: {
        theme: {
            description: 'Global app theme',
            toolbar: {
                title: 'Theme',
                items: [
                    {
                        value: 'normal',
                        title: 'User preference',
                    },
                    {
                        value: 'dark',
                        title: 'Dark',
                    },
                    {
                        value: 'light',
                        title: 'light',
                    },
                ],
            },
        },
    },
    initialGlobals: {
        theme: 'light',
    },
    decorators: [
        (story, context) => {
            const selectedTheme = context.globals.theme
            const html =
                context.canvasElement.ownerDocument.querySelector('html')

            if (html && selectedTheme == 'normal') {
                html.style.removeProperty('--theme')
            } else if (html) {
                html.style.setProperty('--theme', selectedTheme)
            }

            return story(context)
        },
    ],
} satisfies Preview
