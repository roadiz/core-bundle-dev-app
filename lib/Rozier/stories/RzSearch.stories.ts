import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/storybook/renderer/rzButton'
import { rzCardRenderer } from '~/utils/storybook/renderer/rzCard'
import { rzDialogRenderer } from '~/utils/storybook/renderer/rzDialog'
import { rzInputRenderer } from '~/utils/storybook/renderer/rzInput'
import type { Args as DialogArgs } from './RzDialog.stories'

export type Args = {
    value?: string
    action?: string
    placeholder?: string
    resultLength?: number
    dialogData?: DialogArgs
    attributes?: Record<string, string>
}

const COMPONENT_CLASS_NAME = 'rz-search'

const meta: Meta<Args> = {
    title: 'Components/SearchDialog',
    tags: ['autodocs'],
    args: {
        placeholder: 'Search...',
        action: '',
        resultLength: 20,
        dialogData: {
            modal: true,
            header: undefined,
            closedby: 'any',
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
        },
    },
}

export default meta
type Story = StoryObj<Args>

function innerDialogRenderer(args: Args) {
    const form = document.createElement('form')
    form.classList.add(`${COMPONENT_CLASS_NAME}__search-form`)
    form.setAttribute('method', 'GET')
    form.setAttribute('action', args.action || '#')
    form.setAttribute('role', 'search')
    form.setAttribute('aria-label', 'Une entité')
    // form.onsubmit = (event: Event) => {
    //     event.preventDefault()
    // }

    const searchInput = rzInputRenderer({
        type: 'search',
        name: 'searchTerms',
        id: 'nodes-sources-search-input',
        placeholder: args.placeholder,
        value: args.value || '',
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

    if (args.resultLength) {
        for (let i = 1; i <= args.resultLength; i++) {
            const li = document.createElement('li')
            const card = rzCardRenderer({
                tag: 'a',
                title: `Search result item ${i} for query "${args.value}"`,
                attributes: {
                    href: '#',
                },
            })
            li.appendChild(card)
            ul.appendChild(li)
        }
    }

    return [form, ul]
}

function rzSearchRenderer(args: Args) {
    const wrapper = document.createElement('rz-search')
    const attributesEntries = Object.entries(args.attributes || {})
    if (attributesEntries.length) {
        attributesEntries.forEach(([key, value]) => {
            if (typeof value === 'undefined') return
            wrapper.setAttribute(key, value)
        })
    }

    const dialogId = 'search-dialog'
    const dialog = rzDialogRenderer({
        ...args.dialogData,
        dialogId: dialogId,
        innerHTML: innerDialogRenderer(args)
            .map((el) => el.outerHTML)
            .join(''),
    })
    wrapper.appendChild(dialog)

    const button = rzButtonRenderer({
        label: 'Open search',
        attributes: {
            opentarget: dialogId,
        },
    })
    wrapper.appendChild(button)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzSearchRenderer(args)
    },
}

export const DefaultOpen: Story = {
    render: (args) => {
        return rzSearchRenderer(args)
    },
    args: {
        attributes: {
            ['initial-value']: 'test',
        },
    },
}
