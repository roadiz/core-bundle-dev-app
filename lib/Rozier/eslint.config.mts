import js from '@eslint/js'
import globals from 'globals'
import tseslint from 'typescript-eslint'
import { defineConfig } from 'eslint/config'
import storybook from 'eslint-plugin-storybook'
import eslintPluginPrettierRecommended from 'eslint-plugin-prettier/recommended'

export default defineConfig([
    {
        ignores: ['**/vendor/**', '**/*.min.js'],
    },
    {
        files: ['**/*.{js,mjs,cjs,ts,mts,cts}'],
        plugins: { js },
        extends: ['js/recommended'],
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
                $: true, // TODO: remove when jQuery is removed from the project
                jQuery: true, // TODO: remove when jQuery is removed from the project
                UIkit: true, // UIkit global
            },
        },
    },
    tseslint.configs.recommended,
    ...storybook.configs['flat/recommended'],
    eslintPluginPrettierRecommended,
])
