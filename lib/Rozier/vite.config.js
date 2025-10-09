import vue from '@vitejs/plugin-vue2'
import { resolve } from 'path'
import { defineConfig, normalizePath } from 'vite'
import devManifest from './vite-plugins/dev-manifest'
import initCollections from './vite-plugins/iconify/iconify'
import availableRemixIcons from './vite-plugins/iconify/collections/ri'

export default defineConfig(({ mode }) => {
    return {
        base: mode === 'production' ? '/bundles/roadizrozier/' : '/',
        server: {
            cors: true,
            // Make sure this port is the same as in Dockerfile and compose.yml
            port: 5173,
            strictPort: true,
        },
        optimizeDeps: {
            exclude: ['uikit'], // fix a bug on dev mode + CommonJS require() used in UIkit
        },
        build: {
            outDir: normalizePath(
                resolve(__dirname, '../RoadizRozierBundle/public'),
            ),
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
                assets: resolve(__dirname, './app/assets'),
            },
        },
        plugins: [
            vue(),
            devManifest(),
            initCollections([
                {
                    prefix: 'ri',
                    icons: availableRemixIcons,
                },
                {
                    prefix: 'rz',
                    srcDir: 'app/assets/img/icons/rz',
                },
            ]),
        ],
    }
})
