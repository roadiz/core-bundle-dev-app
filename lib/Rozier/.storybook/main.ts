import type { StorybookConfig } from '@storybook/html-vite'

const config: StorybookConfig = {
    stories: [
        '../stories/**/*.mdx',
        '../stories/**/*.stories.@(js|jsx|mjs|ts|tsx)',
    ],
    addons: ['@storybook/addon-docs'],
    framework: {
        name: '@storybook/html-vite',
        options: {},
    },
    staticDirs: ['../public'],
}

export default config
