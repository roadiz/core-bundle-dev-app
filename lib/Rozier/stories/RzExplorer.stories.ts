import type { Meta, StoryObj } from '@storybook/html-vite'
import { delay, http, HttpResponse } from 'msw'
import {
    type RzExplorerOptions,
    rzExplorerRenderer,
} from '~/utils/storybook/renderer/rzExplorer'
import { SHOW_EXPLORER_EVENT } from '~/custom-elements/RzExplorer'

const DOCUMENTS_ENDPOINT = '/rz-admin/ajax/documents/explore'
const FOLDERS_ENDPOINT = '/rz-admin/ajax/folders/explore'

type Args = RzExplorerOptions & {
    /** Opens the filter column once the explorer is shown */
    showFilters?: boolean
}

const IMAGES = [
    'https://images.unsplash.com/photo-1557821552-17105176677c?w=200&h=150&fit=crop',
    'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=200&h=150&fit=crop',
    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=200&h=150&fit=crop',
    'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=200&h=150&fit=crop',
]

const documents = Array.from({ length: 12 }, (_, index) => ({
    id: index + 1,
    classname: `photo-${index + 1}.jpg`,
    displayable: `photo-${index + 1}.jpg`,
    thumbnail80: IMAGES[index % IMAGES.length],
}))

const folders = [
    { id: 1, name: 'Press', children: [] },
    {
        id: 2,
        name: 'Season 2026',
        children: [
            { id: 3, name: 'Posters', children: [] },
            { id: 4, name: 'Programme', children: [] },
        ],
    },
    { id: 5, name: 'Logos', children: [] },
]

const meta: Meta<Args> = {
    title: 'Components/Overlay/Explorer',
    tags: ['autodocs'],
    render: (args) => {
        const explorer = rzExplorerRenderer(args)

        // The explorer opens on the event a drawer dispatches, once it is in the page and
        // its lazily defined element is upgraded
        requestAnimationFrame(async () => {
            await customElements.whenDefined('rz-explorer')
            document.dispatchEvent(
                new CustomEvent(SHOW_EXPLORER_EVENT, {
                    detail: { id: 'story-drawer', acceptEntity: 'document' },
                }),
            )
            if (args.showFilters) {
                explorer
                    .querySelector<HTMLElement>('[data-filter-toggle]')
                    ?.click()
            }
        })

        return explorer
    },
    args: {
        showFilters: false,
    },
    decorators: [
        (story) => {
            window.RozierConfig = {
                ...(window.RozierConfig || {}),
                ajaxToken: 'story-token',
                routes: {
                    ...(window.RozierConfig?.routes || {}),
                    documentsAjaxExplorer: DOCUMENTS_ENDPOINT,
                    foldersAjaxExplorer: FOLDERS_ENDPOINT,
                },
            }

            return story()
        },
    ],
    parameters: {
        msw: {
            handlers: [
                http.get(DOCUMENTS_ENDPOINT, () =>
                    HttpResponse.json({
                        documents,
                        filters: { itemCount: documents.length, nextPage: 1 },
                    }),
                ),
                http.get(FOLDERS_ENDPOINT, () =>
                    HttpResponse.json({ folders }),
                ),
            ],
        },
        // The explorer is fixed to the viewport: one iframe per story
        docs: { story: { inline: false, iframeHeight: 640 } },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

/** The filter column widens the explorer; the items keep their width. */
export const WithFilters: Story = {
    args: { showFilters: true },
}

/** The documents endpoint never answers: the loader stays over the items. */
export const Loading: Story = {
    parameters: {
        msw: {
            handlers: [
                http.get(DOCUMENTS_ENDPOINT, async () => {
                    await delay('infinite')
                    return HttpResponse.json({})
                }),
                http.get(FOLDERS_ENDPOINT, () =>
                    HttpResponse.json({ folders }),
                ),
            ],
        },
    },
}
