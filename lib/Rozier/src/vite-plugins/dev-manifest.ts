import { existsSync, mkdirSync, writeFileSync, rmSync } from 'fs'
import path from 'path'
import { Plugin, normalizePath } from 'vite'

export interface DevManifestPluginConfig {
    omitInputs?: string[]
    manifestName?: string
    delay?: number
    clearOnClose?: boolean
}

export interface DevManifestEntry {
    file?: string
    name?: string
    src?: string
    isEntry?: boolean
    imports?: string[]
    css?: string
    assets?: string[]
}

export type DevManifest = Record<string, DevManifestEntry>

const MANIFEST_NAME = 'manifest.dev'

const createSimplifyPath = (root: string, base: string) => (path: string) => {
    path = normalizePath(path)

    if (root !== '/' && path.startsWith(root)) {
        path = path.slice(root.length)
    }

    if (path.startsWith(base)) {
        path = path.slice(base.length)
    }

    if (path[0] === '/') {
        path = path.slice(1)
    }

    return path
}

const createEntry = (filePath: string, options?: { name?: string; base?: string }): DevManifestEntry => {
    return {
        file: new URL(filePath, options?.base).href,
        name: options?.name,
        src: filePath,
        isEntry: true,
        imports: [],
        css: undefined,
        assets: [],
    }
}

// Inspired by https://github.com/owlsdepartment/vite-plugin-dev-manifest
const plugin = ({
    omitInputs = [],
    manifestName = MANIFEST_NAME,
    delay,
    clearOnClose = true,
}: DevManifestPluginConfig = {}): Plugin => ({
    name: 'dev-manifest',
    enforce: 'post',
    apply: 'serve',
    configureServer(server) {
        const { config, httpServer } = server

        if (!config.build.manifest) {
            return
        }

        httpServer?.once('listening', () => {
            const { root: _root, base } = config
            const root = normalizePath(_root)
            const protocol = config.server.https ? 'https' : 'http'
            const host = server.config.server.host ?? 'localhost'
            const port = config.server.port
            const manifest: DevManifest = {}
            const inputOptions = config.build.rollupOptions?.input ?? {}
            const simplifyPath = createSimplifyPath(root, base)
            const origin = `${protocol}://${host}:${port}`
            const entryOptions = {
                base: origin,
            }

            config.server.origin = origin

            manifest['@vite/client'] = createEntry('@vite/client', { ...entryOptions, name: '@vite/client' })

            if (typeof inputOptions === 'string') {
                const path = simplifyPath(inputOptions)

                manifest[path] = createEntry(path, entryOptions)
            } else if (Array.isArray(inputOptions)) {
                for (const name of inputOptions) {
                    if (omitInputs.includes(name)) continue

                    const path = simplifyPath(name)

                    manifest[path] = createEntry(path, entryOptions)
                }
            } else {
                for (const [entryName, entryPath] of Object.entries(inputOptions)) {
                    if (omitInputs.includes(entryName)) continue

                    const path = simplifyPath(entryPath)

                    manifest[path] = createEntry(path, { ...entryOptions, name: entryName })
                }
            }

            const outputDir = path.isAbsolute(config.build.outDir)
                ? config.build.outDir
                : path.resolve(config.root, config.build.outDir)

            if (!existsSync(outputDir)) {
                mkdirSync(outputDir, { recursive: true })
            }

            const writeManifest = () => {
                writeFileSync(path.resolve(outputDir, `${manifestName}.json`), JSON.stringify(manifest, null, '\t'))
            }

            if (delay !== undefined && typeof delay === 'number') {
                setTimeout(() => writeManifest(), delay)
            } else {
                writeManifest()
            }
        })

        // Remove manifest file on server close
        const removeManifest = () => {
            if (!clearOnClose) return

            const outputDir = path.isAbsolute(config.build.outDir)
                ? config.build.outDir
                : path.resolve(config.root, config.build.outDir)
            const manifestPath = path.resolve(outputDir, `${manifestName}.json`)

            if (existsSync(manifestPath)) rmSync(manifestPath)
        }
        const cleanup = () => {
            removeManifest()
            removeCloseListeners()
        }
        const onClose = cleanup
        const onProcessEnd = () => {
            cleanup()
            process.exit()
        }

        // Attach event listeners
        httpServer?.once('close', onClose)
        process.once('SIGINT', onProcessEnd)
        process.once('SIGTERM', onProcessEnd)

        // Cleanup event listeners
        function removeCloseListeners() {
            httpServer?.off?.('close', onClose)
            process.off('SIGINT', onProcessEnd)
            process.off('SIGTERM', onProcessEnd)
        }
    },
})

export default plugin
