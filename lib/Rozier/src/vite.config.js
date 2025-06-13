import { resolve } from 'path'
import { normalizePath } from 'vite'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue2'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig(() => {
    return {
        // server: {
        //     port: 8681,
        // },
        // assetsInclude: ['**/*.twig'],
        build: {
            outDir: normalizePath(resolve(__dirname, './static')),
            emptyOutDir: true,
            // lib: {
            //     entry: {
            //         main: resolve(__dirname, 'Resources/app/main.js'),
            //         simple: resolve(__dirname, 'Resources/app/simple.js'),
            //     },
            //     name: 'Rozier',
            // },
            rollupOptions: {
                // make sure to externalize deps that shouldn't be bundled
                // into your library
                external: ['jquery'],
                input: {
                    main: resolve(__dirname, 'Resources/app/main.js'),
                    simple: resolve(__dirname, 'Resources/app/simple.js')
                },
                output: {
                    // Provide global variables to use in the UMD build
                    // for externalized deps
                    globals: {
                        jquery: 'jquery',
                        $: 'jquery',
                    },
                    entryFileNames: `[name]-[hash].js`,
                    chunkFileNames: `[name]-[hash].js`,
                    assetFileNames: `[name]-[hash].[ext]`
                },
            },
        },
        plugins: [
            vue(),
            viteStaticCopy({
                targets: [
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/jquery/dist/jquery.min.js')),
                        dest: normalizePath(resolve(__dirname, './static/vendor')),
                    },
                    {
                        src: normalizePath(resolve(__dirname, './node_modules/jquery/dist/jquery.js')),
                        dest: normalizePath(resolve(__dirname, './static/vendor')),
                    },
                ],
            }),
        ],
    }
})
