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
            host: true, // listen on all interfaces, required inside Docker
            // Make sure this port is the same as in Dockerfile and compose.yml
            port: 5173,
            strictPort: true,
            // URL the browser uses to reach this server (the app runs on another origin)
            origin: process.env.VITE_DEV_ORIGIN ?? 'http://localhost:5173',
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
                    prefix: 'ri', // Specific iconify module need to be added `@iconify-json/${prefix}`
                    icons: availableRemixIcons,
                },
                {
                    prefix: 'rz',
                    srcDir: 'app/assets/img/icons/rz',
                },
                {
                    prefix: 'logo',
                    srcDir: 'app/assets/img/icons/logo',
                },
            ]),
        ],
    }
})
