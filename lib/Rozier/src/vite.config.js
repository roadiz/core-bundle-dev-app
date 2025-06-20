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
                input: {
                    main: resolve(__dirname, 'Resources/app/main.js'),
                    simple: resolve(__dirname, 'Resources/app/simple.js'),
                },
                output: {
                    entryFileNames: `[name]-[hash].js`,
                    chunkFileNames: `[name]-[hash].js`,
                    assetFileNames: `[name]-[hash].[ext]`,
                    manualChunks: {
                        jquery: ['jquery'],
                        vue: ['vue', 'vuex'],
                    },
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
