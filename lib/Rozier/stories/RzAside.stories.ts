import type { Meta, StoryObj } from '@storybook/html-vite'
import { delay, http, HttpResponse } from 'msw'
import {
    type RzAsideOptions,
    rzAsideRenderer,
    rzAsideTreeWrapperRenderer,
    TREE_TYPES,
} from '~/utils/storybook/renderer/rzAside'

type Args = RzAsideOptions

const TREE_AJAX_GATEWAY = '/rz-admin/ajax/tree'

// Read by the mocked gateway: rz-aside fetches its tree once connected
let currentArgs: Args | null = null

const meta: Meta<Args> = {
    title: 'Patterns/Aside',
    tags: ['autodocs'],
    render: (args) => {
        currentArgs = args
        window.RozierConfig = {
            ...(window.RozierConfig || {}),
            ajaxToken: 'storybook-token',
            routes: {
                ...(window.RozierConfig?.routes || {}),
                treeAjaxGateway: TREE_AJAX_GATEWAY,
            },
        }
        const container = document.createElement('div')
        container.style.cssText = 'width: 344px; height: 600px;'
        const aside = rzAsideRenderer(args)
        aside.style.height = '100%'
        container.appendChild(aside)
        return container
    },
    args: {
        defaultTranslationId: '1',
        treeType: 'node',
        tree: {
            items: [
                {
                    label: 'Home',
                    iconClass: 'rz-icon-ri--home-2-fill',
                    expanded: true,
                    children: [
                        {
                            label: 'About us',
                            iconClass: 'rz-icon-rz--status-published-fill',
                        },
                        {
                            label: 'Contact',
                            iconClass: 'rz-icon-rz--status-draft-fill',
                        },
                    ],
                },
                {
                    label: 'Blog',
                    iconClass: 'rz-icon-rz--status-published-fill',
                    children: [
                        {
                            label: 'First post',
                            iconClass: 'rz-icon-rz--status-draft-fill',
                        },
                    ],
                },
            ],
        },
    },
    argTypes: {
        treeType: { control: 'select', options: TREE_TYPES },
    },
    parameters: {
        msw: {
            handlers: [
                http.get(TREE_AJAX_GATEWAY, () => {
                    const treeType = currentArgs?.treeType || 'node'
                    return HttpResponse.json({
                        tree_type: treeType,
                        [`${treeType}Tree`]: rzAsideTreeWrapperRenderer({
                            treeType,
                            tree: currentArgs?.tree,
                        }).outerHTML,
                    })
                }),
            ],
        },
        // One iframe per story: rz-aside uses a fixed ID
        docs: { story: { inline: false, iframeHeight: 640 } },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

/** The gateway never answers: the loading state rendered by rz_aside.html.twig stays. */
export const Loading: Story = {
    parameters: {
        controls: { include: [] },
        msw: {
            handlers: [
                http.get(TREE_AJAX_GATEWAY, async () => {
                    await delay('infinite')
                    return HttpResponse.json({})
                }),
            ],
        },
    },
}
