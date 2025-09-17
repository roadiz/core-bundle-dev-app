import vue from '@vitejs/plugin-vue2'
import { resolve } from 'path'
import { defineConfig, normalizePath } from 'vite'
import devManifest from './vite-plugins/dev-manifest'

export default defineConfig(({ mode }) => {
    return {
        base: mode === 'production' ? '/bundles/roadizrozier/' : '/',
        server: {
            cors: true,
        },
        optimizeDeps: {
            exclude: ['uikit'], // fix a bug on dev mode + CommonJS require() used in UIkit
        },
        build: {
            outDir: normalizePath(resolve(__dirname, '../RoadizRozierBundle/public')),
            emptyOutDir: true,
            manifest: 'manifest.json',
            rollupOptions: {
                input: {
                    main: resolve(__dirname, 'app/main.js'),
                    simple: resolve(__dirname, 'app/simple.js'),
                    shared: resolve(__dirname, 'app/shared.js'),
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
                '~': resolve(__dirname, './app'),
            },
        },
        plugins: [vue(), devManifest()],
    }
})
