import js from '@eslint/js'
import globals from 'globals'
import tseslint from 'typescript-eslint'
import { defineConfig } from 'eslint/config'
import stylistic from '@stylistic/eslint-plugin'

export default defineConfig([
  {
    ignores: ['**/vendor/**', '**/*.min.js'],
  },
  {
    files: ['**/*.{js,mjs,cjs,ts,mts,cts}'],
    plugins: { js, '@stylistic': stylistic },
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
  stylistic.configs.recommended,
  tseslint.configs.recommended,
])
