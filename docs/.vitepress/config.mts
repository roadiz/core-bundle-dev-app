import {defineConfig} from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "docs",
    description: "Roadiz Documentation",
    themeConfig: {
        siteTitle: 'Roadiz',
        logo: '/Roadiz_White.jpg',
        // https://vitepress.dev/reference/default-theme-config
        nav: [
            {text: 'Home', link: '/'},
            {text: 'Developer', link: '/developer/index'},
        ],

        search: {
            provider: 'local'
        },

        sidebar: [
            {
                text: 'Developer',
                items: [
                    {
                        text: 'First steps',
                        collapsed: true,
                        items: [
                            {text: 'Requirements', link: '/developer/first-steps/requirements'},
                            {text: 'Installation', link: '/developer/first-steps/installation'},
                            {text: 'Manual configuration', link: '/developer/first-steps/manual_config'},
                            {text: 'Upgrading', link: '/developer/first-steps/upgrading'},
                        ]
                    },
                    {
                        text: 'Node system',
                        collapsed: true,
                        items: [
                            {text: 'Intro', link: '/developer/nodes-system/index'},
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
                            {text: 'Intro', link: '/developer/api/index'},
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
            }
        ],

        editLink: {
            pattern: 'https://github.com/roadiz/core-bundle-dev-app/docs/::path',
            text: 'Edit this page on GitHub'
        },

        socialLinks: [
            { icon: 'github', link: 'https://github.com/roadiz' },
        ],

        footer: {
            copyright: '© Copyright 2013-present, Ambroise Maupate & Julien Blanchet.'
        }
    }
})
