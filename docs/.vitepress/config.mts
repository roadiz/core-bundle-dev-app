import { fileURLToPath } from 'node:url';
import {defineConfig, loadEnv} from 'vitepress'

export default ({ mode }) => {
    const env = loadEnv(mode, fileURLToPath(new URL('../', import.meta.url)));

    /**
     * Use VITE_CONFIG_BASE to deploy in a subdirectory.
     */

    return defineConfig({
        title: env.VITE_CONFIG_BASE ? "Roadiz Documentation ("+env.VITE_CONFIG_BASE+")" : "Roadiz Documentation",
        description: env.VITE_CONFIG_BASE ? "Roadiz Documentation ("+env.VITE_CONFIG_BASE+")" : "Roadiz Documentation",
        base: env.VITE_CONFIG_BASE ? '/'+env.VITE_CONFIG_BASE+'/' : '/',
        outDir: env.VITE_CONFIG_BASE ? './.vitepress/dist/'+env.VITE_CONFIG_BASE : './.vitepress/dist',
        cacheDir: env.VITE_CONFIG_BASE ? './.vitepress/cache/'+env.VITE_CONFIG_BASE : './.vitepress/cache',
        lastUpdated: true,
        head: [['link', { rel: 'icon', href: '/favicon.ico' }]],
        themeConfig: {
            siteTitle: 'Roadiz',
            logo: '/Roadiz_White.jpg',

            nav: [
                {
                    text: 'Versions',
                    items: [
                        {
                            text: 'Latest',
                            link: 'https://docs.roadiz.io/'
                        },
                        {
                            text: 'Develop',
                            link: 'https://docs.roadiz.io/develop/'
                        },
                    ]
                },
                {
                    text: 'Other documentation',
                    items: [
                        {
                            text: 'Project changelog',
                            link: 'https://github.com/roadiz/core-bundle-dev-app/blob/develop/CHANGELOG.md'
                        },
                        {
                            text: 'Upgrade notes',
                            link: 'https://github.com/roadiz/core-bundle-dev-app/blob/develop/UPGRADE.md'
                        },
                    ]
                },
                { text: 'Built by Rezo Zero', link: 'https://www.rezo-zero.com/' },
            ],

            search: {
                provider: 'local'
            },

            sidebar: [
                {
                    text: 'Developer',
                    items: [
                        { text: 'Requirements', link: '/developer/first-steps/requirements' },
                        { text: 'Installation', link: '/developer/first-steps/installation' },
                        { text: 'Configuration', link: '/developer/first-steps/manual_config' },
                        { text: 'Solr search engine', link: '/developer/first-steps/use_apache_solr' },
                        { text: 'Upgrading', link: '/developer/first-steps/upgrading' },
                        { text: 'Security', link: '/developer/security/security' },
                        {
                            text: 'Node system',
                            collapsed: true,
                            items: [
                                { text: 'Introduction', link: '/developer/nodes-system/intro' },
                                { text: 'Nodes', link: '/developer/nodes-system/nodes' },
                                { text: 'Managing node-types', link: '/developer/nodes-system/node_types' },
                                {
                                    text: 'Node-type fields specifications',
                                    link: '/developer/nodes-system/node_type_fields'
                                },
                                { text: 'Node-type decorators', link: '/developer/nodes-system/node_type_decorators' },
                            ]
                        },
                        {
                            text: 'Building headless websites using API',
                            collapsed: true,
                            items: [
                                { text: 'Introduction', link: '/developer/api/intro' },
                                { text: 'Exposing node types', link: '/developer/api/exposing_node_types' },
                                { text: 'Serialization', link: '/developer/api/serialization' },
                                { text: 'Web response', link: '/developer/api/web_response' },
                            ]
                        },
                        { text: 'Tag system', link: '/developer/tags-system/' },
                        { text: 'Documents system', link: '/developer/documents-system/' },
                        { text: 'Attributes', link: '/developer/attributes/' },
                        {
                            text: 'Forms',
                            collapsed: true,
                            items: [
                                { text: 'Contact forms', link: '/developer/forms/contact_forms' },
                                { text: 'Custom forms', link: '/developer/forms/custom_forms' },
                            ]
                        },
                        { text: 'Infrastructure', link: '/developer/infrastructure/infrastructure' },
                        { text: 'Contributing', link: '/developer/contributing/contributing' },
                        { text: 'Troubleshooting', link: '/developer/troubleshooting/troubleshooting' },
                    ]
                },
                {
                    text: 'User',
                    collapsed: false,
                    items: [
                        { text: 'Introduction', link: '/user/intro' },
                        { text: 'Se connecter au back office', link: '/user/connecter_au_back_office' },
                        { text: 'Édition des contenus', link: '/user/edition_des_contenus' },
                        { text: 'Gérer les médias', link: '/user/gerer_les_medias' },
                        { text: 'Dossiers de documents', link: '/user/dossiers_de_documents' },
                        { text: 'Étiquettes', link: '/user/etiquettes' },
                        { text: 'Syntaxe Markdown', link: '/user/syntaxe_markdown' },
                        { text: 'Gérer les comptes', link: '/user/gerer_les_comptes' },
                        { text: 'Formulaires personnalisés', link: '/user/formulaires_personnalises' },
                        { text: 'États (publié, dépublié, caché)', link: '/user/etats' },
                        { text: 'Visualisation et Prévisualisation', link: '/user/visualisation_et_previsualisation' },
                    ]
                },
                {
                    text: 'Extensions',
                    collapsed: false,
                    items: [
                        { text: 'Roadiz events', link: '/extensions/events' },
                        { text: 'Extending Roadiz', link: '/extensions/extending_roadiz' },
                        { text: 'Extending Solr', link: '/extensions/extending_solr' },
                    ]
                }
            ],

            editLink: {
                pattern: 'https://github.com/roadiz/core-bundle-dev-app/edit/develop/docs/:path',
                text: 'Edit this page on GitHub'
            },

            socialLinks: [
                { icon: 'github', link: 'https://github.com/roadiz' },
            ],

            footer: {
                copyright: 'Roadiz is proudly maintained by Rezo Zero Team. © Copyright 2013-present, Ambroise Maupate, Julien Blanchet.'
            }
        }
    })
}
