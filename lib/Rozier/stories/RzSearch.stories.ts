import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzCardRenderer } from '~/utils/storybook/renderer/rzCard'
import { rzDialogWrapperRenderer } from '~/utils/storybook/renderer/rzDialog'
import { rzInputRenderer } from '~/utils/storybook/renderer/rzInput'

export type Args = {
    query: string
    placeholder?: string
    disabled?: boolean
}

const COMPONENT_CLASS_NAME = 'rz-search'

const meta: Meta<Args> = {
    title: 'Components/SearchDialog',
    tags: ['autodocs'],
    args: {
        query: '',
        placeholder: 'Search...',
    },
}

export default meta
type Story = StoryObj<Args>

function dialogRenderer(args: Args) {
    const form = document.createElement('form')
    form.classList.add(`${COMPONENT_CLASS_NAME}__search-form`)
    form.setAttribute('role', 'search')
    form.setAttribute('aria-label', 'Une entité')

    const searchInput = rzInputRenderer({
        type: 'search',
        name: 'searchTerms',
        id: 'nodes-sources-search-input',
        placeholder: args.placeholder,
        value: args.query,
    })
    form.appendChild(searchInput)

    const ul = document.createElement('ul')
    ul.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    /* A11Y NOTE :
    An non displayed element (visibility-hidden) could be added to describe accessibility informations.
    - search status: wainting for query, searching, no result found
    - result count: displaying
    e.g: <span class="visibility-hidden" aria-live="polite" aria-atomic="true">10 results found for query "test"</span>
     */

    for (let i = 1; i <= 10; i++) {
        const li = document.createElement('li')
        const card = rzCardRenderer({
            tag: 'a',
            title: `Search result item ${i} for query "${args.query}"`,
            attributes: {
                href: '#',
            },
        })
        li.appendChild(card)
        ul.appendChild(li)
    }

    return [form, ul]
}

function rzSearchRenderer(args: Args) {
    const wrapper = rzDialogWrapperRenderer({
        modal: true,
        header: undefined,
        closedby: 'any',
        innerHTML: dialogRenderer(args)
            .map((el) => el.outerHTML)
            .join(''),
        footer: {
            justifyEnd: true,
            buttons: [
                {
                    label: 'Advanced search',
                    emphasis: 'primary',
                    attributes: {
                        class: 'rz-dialog__item--end',
                    },
                },
            ],
        },
        dialogId: 'search-dialog',
    })
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzSearchRenderer(args)
    },
}
