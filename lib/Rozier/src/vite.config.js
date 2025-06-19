import { resolve } from 'path'
import { normalizePath } from 'vite'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue2'

export default defineConfig(() => {
    return {
        base: '/bundles/roadizrozier/',
        build: {
            outDir: normalizePath(resolve(__dirname, '../../RoadizRozierBundle/public')),
            emptyOutDir: true,
            manifest: 'manifest.json',
            rollupOptions: {
                // Make sure to externalize deps that shouldn't be bundled
                external: ['jquery'],
                input: {
                    main: resolve(__dirname, 'Resources/app/main.js'),
                    simple: resolve(__dirname, 'Resources/app/simple.js'),
                },
                output: {
                    // Provide global variables to use in the UMD build for externalized deps
                    globals: {
                        jquery: 'jquery',
                        $: 'jquery',
                    },
                    entryFileNames: `[name]-[hash].js`,
                    chunkFileNames: `[name]-[hash].js`,
                    assetFileNames: `[name]-[hash].[ext]`,
                },
            },
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm.js',
            },
        },
        plugins: [vue()],
    }
})
