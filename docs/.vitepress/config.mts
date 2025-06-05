import {defineConfig} from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "Roadiz Documentation",
    description: "Roadiz Documentation",
    lastUpdated: true,
    head: [['link', { rel: 'icon', href: '/favicon.ico' }]],
    themeConfig: {
        siteTitle: 'Roadiz',
        logo: '/Roadiz_White.jpg',

        nav: [
            {text: 'Home', link: '/'},
            {
                text: 'Other documentation',
                items: [
                    {text: 'Project changelog', link: 'https://github.com/roadiz/core-bundle-dev-app/blob/develop/CHANGELOG.md'},
                    {text: 'Upgrade notes', link: 'https://github.com/roadiz/core-bundle-dev-app/blob/develop/UPGRADE.md'},
                ]
            },
            {text: 'Built by Rezo Zero', link: 'https://www.rezo-zero.com/'},
        ],

        search: {
            provider: 'local'
        },

        sidebar: [
            {
                text: 'Developer',
                items: [
                    {text: 'Requirements', link: '/developer/first-steps/requirements'},
                    {text: 'Installation', link: '/developer/first-steps/installation'},
                    {text: 'Configuration', link: '/developer/first-steps/manual_config'},
                    {text: 'Upgrading', link: '/developer/first-steps/upgrading'},
                    {
                        text: 'Node system',
                        collapsed: true,
                        items: [
                            {text: 'Introduction', link: '/developer/nodes-system/intro'},
                            {text: 'Nodes', link: '/developer/nodes-system/nodes'},
                            {text: 'Managing node-types', link: '/developer/nodes-system/node_types'},
                            {text: 'Node-type fields specifications', link: '/developer/nodes-system/node_type_fields'},
                            {text: 'Node-type decorators', link: '/developer/nodes-system/node_type_decorators'},
                        ]
                    },
                    {
                        text: 'Building headless websites using API',
                        collapsed: true,
                        items: [
                            {text: 'Introduction', link: '/developer/api/index'},
                            {text: 'Exposing node types', link: '/developer/api/exposing_node_types'},
                            {text: 'Serialization', link: '/developer/api/serialization'},
                            {text: 'Web response', link: '/developer/api/web_response'},
                        ]
                    },
                    {text: 'Tag system', link: '/developer/tags-system/'},
                    {text: 'Documents system', link: '/developer/documents-system/'},
                    {text: 'Attributes', link: '/developer/attributes/'},
                    {
                        text: 'Forms',
                        collapsed: true,
                        items: [
                            {text: 'Contact forms', link: '/developer/forms/contact_forms'},
                            {text: 'Custom forms', link: '/developer/forms/custom_forms'},
                        ]
                    },
                    {text: 'Contributing', link: '/developer/contributing/contributing'},
                    {text: 'Troubleshooting', link: '/developer/troubleshooting/troubleshooting'},
                ]
            },
            {
                text: 'User',
                collapsed: false,
                items: [
                    {text: 'Introduction', link: '/user/intro'},
                    {text: 'Se connecter au back office', link: '/user/connecter_au_back_office'},
                    {text: 'Édition des contenus',  link: '/user/edition_des_contenus'},
                    {text: 'Gérer les médias',  link: '/user/gerer_les_medias'},
                    {text: 'Dossiers de documents',  link: '/user/dossiers_de_documents'},
                    {text: 'Étiquettes', link: '/user/etiquettes'},
                    {text: 'Syntaxe Markdown', link: '/user/syntaxe_markdown'},
                    {text: 'Gérer les comptes',  link: '/user/gerer_les_comptes'},
                    {text: 'Formulaires personnalisés', link: '/user/formulaires_personnalises'},
                    {text: 'États (publié, dépublié, caché)', link: '/user/etats'},
                    {text: 'Visualisation et Prévisualisation',  link: '/user/visualisation_et_previsualisation'},
                ]
            },
            {
                text: 'Extensions',
                collapsed: false,
                items: [
                    {text: 'Roadiz events', link: '/extensions/events'},
                    {text: 'Extending Roadiz', link: '/extensions/extending_roadiz'},
                    {text: 'Extending Solr', link: '/extensions/extending_solr'},
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
