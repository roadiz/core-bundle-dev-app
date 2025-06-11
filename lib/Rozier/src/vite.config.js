import { resolve } from 'path'
import { normalizePath } from 'vite'
import { defineConfig, loadEnv } from 'vite'
import { createHtmlPlugin } from 'vite-plugin-html'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig(({ mode }) => {
    // Load env file based on `mode` in the current working directory.
    // Set the third parameter to '' to load all env regardless of the
    // `VITE_` prefix.
    const env = loadEnv(mode, process.cwd(), '')

    return {
        define: {
            'process.env.NODE_ENV': JSON.stringify(env.APP_ENV),
        },
        server: {
            port: 8681,
        },
        plugins: [
            // TODO: resolved error - Failed to parse source for import analysis because the content contains invalid JS
            // syntax. You may need to install appropriate plugins to handle the .twig file format, or if it's an asset,
            // add "**/*.twig" to `assetsInclude` in your configuration.
            createHtmlPlugin({
                minify: true,
                entry: normalizePath(resolve(__dirname, './Resources/views/partials/css-inject-src.html.twig')),
                template: normalizePath(resolve(__dirname, './Resources/views/partials/css-inject.html.twig')),
            }),
            createHtmlPlugin({
                minify: true,
                entry: normalizePath(resolve(__dirname, './Resources/views/partials/js-inject-src.html.twig')),
                template: normalizePath(resolve(__dirname, './Resources/views/partials/js-inject.html.twig')),
            }),
            createHtmlPlugin({
                minify: true,
                entry: normalizePath(resolve(__dirname, './Resources/views/partials/simple-js-inject-src.html.twig')),
                template: normalizePath(resolve(__dirname, './Resources/views/partials/simple-js-inject.html.twig')),
            }),
            viteStaticCopy({
                targets: [
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/vue/dist/vue.min.js')),
                        dest: '~/static/vendor',
                    },
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/vue/dist/vue.js')),
                        dest: '~/static/vendor',
                    },
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/vuex/dist/vuex.min.js')),
                        dest: '~/static/vendor',
                    },
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/vuex/dist/vuex.js')),
                        dest: '~/static/vendor',
                    },
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/jquery/dist/jquery.min.js')),
                        dest: '~/static/vendor',
                    },
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/jquery/dist/jquery.js')),
                        dest: '~/static/vendor',
                    },
                ],
            }),
        ],
    }
})
