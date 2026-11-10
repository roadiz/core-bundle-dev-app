import type { Meta, StoryObj } from '@storybook/html-vite'
import { http, HttpResponse } from 'msw'
import {
    RZ_AUTOCOMPLETE_ACTIONS,
    type RzAutocompleteAction,
    type RzAutocompleteOptions,
    rzAutocompleteRenderer,
} from '~/utils/storybook/renderer/rzAutocomplete'

type Args = RzAutocompleteOptions

const SEARCH_PATH = '/rz-admin/ajax/autocomplete'

const SUGGESTIONS: Record<RzAutocompleteAction, string[]> = {
    tagAutocomplete: [
        'news',
        'news/culture',
        'news/culture/theatre',
        'news/sport',
        'newsletter',
    ],
    folderAutocomplete: ['photos', 'photos/2025', 'photos/press', 'posters'],
}

/*
 * Type two letters to open the suggestion menu (mocked search).
 */
const meta: Meta<Args> = {
    title: 'Components/Form/Autocomplete',
    tags: ['autodocs'],
    render: (args) => rzAutocompleteRenderer(args),
    args: {
        action: 'tagAutocomplete',
        searchPath: SEARCH_PATH,
        csrfToken: 'storybook',
        placeholder: 'Tags to link or unlink',
    },
    argTypes: {
        action: { control: 'select', options: RZ_AUTOCOMPLETE_ACTIONS },
    },
    parameters: {
        msw: {
            handlers: [
                http.get(SEARCH_PATH, ({ request }) => {
                    const params = new URL(request.url).searchParams
                    const search = params.get('search')?.toLowerCase() || ''
                    const action = params.get('_action') as RzAutocompleteAction
                    const values = SUGGESTIONS[action] || []

                    return HttpResponse.json(
                        values.filter((value) => value.includes(search)),
                    )
                }),
            ],
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const Folder: Story = {
    args: {
        action: 'folderAutocomplete',
        placeholder: 'Folders to link or unlink',
    },
}
