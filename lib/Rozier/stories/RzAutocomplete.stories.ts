import type { Meta, StoryObj } from '@storybook/html-vite'
import { http, HttpResponse } from 'msw'

type Args = {
    element: 'tag-autocomplete' | 'folder-autocomplete'
    placeholder: string
}

const SEARCH_PATH = '/rz-admin/ajax/autocomplete'

const SUGGESTIONS: Record<string, string[]> = {
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
 * <tag-autocomplete> and <folder-autocomplete> wrap the Symfony form of the node and
 * document bulk actions. Type two letters to open the suggestion menu (mocked search).
 */
const meta: Meta<Args> = {
    title: 'Components/Form/Autocomplete',
    tags: ['autodocs'],
    render: (args) => {
        const host = document.createElement(args.element)
        host.setAttribute('data-search-path', SEARCH_PATH)
        host.setAttribute('data-csrf-token', 'storybook')

        const input = document.createElement('input')
        input.type = 'text'
        input.className = `rz-input rz-${args.element}`
        input.placeholder = args.placeholder
        host.appendChild(input)

        return host
    },
    args: {
        element: 'tag-autocomplete',
        placeholder: 'Tags to link or unlink',
    },
    argTypes: {
        element: {
            control: 'select',
            options: ['tag-autocomplete', 'folder-autocomplete'],
        },
    },
    parameters: {
        msw: {
            handlers: [
                http.get(SEARCH_PATH, ({ request }) => {
                    const params = new URL(request.url).searchParams
                    const search = params.get('search')?.toLowerCase() || ''
                    const values =
                        SUGGESTIONS[params.get('_action') || ''] || []

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
        element: 'folder-autocomplete',
        placeholder: 'Folders to link or unlink',
    },
}
