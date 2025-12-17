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
                        value: 'light dark',
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
            const html =
                context.canvasElement.ownerDocument.querySelector('html')

            if (html) {
                html.style.colorScheme = context.globals.theme
            }

            return story(context)
        },
    ],
} satisfies Preview
