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
                            {text: 'Node types', link: '/developer/nodes-system/node_types'},
                            {text: 'Node type fields', link: '/developer/nodes-system/node_type_fields'},
                            {text: 'Node type decorators', link: '/developer/nodes-system/node_type_decorators'},
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
                    {text: 'Introduction', link: '/user/Intro'},
                    {text: 'Se connecter au back office', link: '/user/Se%20connecter%20au%20back-office'},
                    {text: 'Édition des contenus',  link: '/user/Édition%20des%20contenus'},
                    {text: 'Gérer les médias',  link: '/user/Gérer%20les%20médias'},
                    {text: 'Dossiers de documents',  link: '/user/Dossiers%20de%20documents'},
                    {text: 'Étiquettes', link: '/user/Étiquettes'},
                    {text: 'Syntaxe Markdown', link: '/user/Syntaxe%20Markdown'},
                    {text: 'Gérer les comptes',  link: '/user/Gérer%20les%20comptes'},
                    {text: 'Formulaires personnalisés', link: '/user/Formulaires%20personnalisés'},
                    {text: 'États (publié, dépublié, caché)', link: '/user/États%20(publié,%20dépublié,%20caché)'},
                    {text: 'Visualisation et Prévisualisation',  link: '/user/Visualisation%20et%20Prévisualisation'},
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
