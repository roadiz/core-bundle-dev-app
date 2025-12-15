import type { Meta, StoryObj } from '@storybook/html-vite'
// import { rzCardRenderer } from '~/utils/storybook/renderer/rzCard'
import { rzDialogRenderer } from '~/utils/storybook/renderer/rzDialog'
import { rzInputRenderer } from '~/utils/storybook/renderer/rzInput'
import type { Args as DialogArgs } from './RzDialog.stories'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export type Args = RzElement & {
    value?: string
    action?: string
    placeholder?: string
    dialogData: DialogArgs & Required<Pick<DialogArgs, 'dialogId'>>
}

const COMPONENT_CLASS_NAME = 'rz-search'

const meta: Meta<Args> = {
    title: 'Components/Overlay/SearchDialog',
    tags: ['autodocs'],
    args: {
        placeholder: 'Search...',
        action: '',
        dialogData: {
            modal: true,
            header: undefined,
            closedby: 'any',
            dialogId: 'search-dialog',
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
    decorators: [
        (story) => {
            window.RozierConfig = {
                ...(window.RozierConfig || {}),
                ajaxToken:
                    'df5fd31.BsNTK_iUS4RuAXYJCY4aKyP8PhT2eIvrWI7Tpc1H0-s.P48GGq_Aev4mbx1KMcNeeUWQX0G9E--cNfq_3KsDhKdXkDl5svUZzwpsRg',
                routes: {
                    searchAjax: '/rz-admin/ajax/search',
                },
            }

            return story()
        },
    ],
}

export default meta
type Story = StoryObj<Args>

function innerDialogRenderer(args: Args) {
    const form = document.createElement('form')
    form.classList.add(`${COMPONENT_CLASS_NAME}__search-form`)
    form.setAttribute('method', 'GET')
    form.setAttribute(
        'action',
        args.action || window.RozierConfig.routes?.searchAjax || '',
    )
    form.setAttribute('prevent-submit', '')
    form.setAttribute('role', 'search')
    form.setAttribute('aria-label', 'Une entité')

    const searchInput = rzInputRenderer({
        type: 'search',
        name: 'searchTerms',
        id: 'nodes-sources-search-input',
        placeholder: args.placeholder,
        value: args.value || '',
    })
    form.appendChild(searchInput)

    const ul = document.createElement('ul')
    ul.setAttribute('data-search-list', '')
    ul.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    /* A11Y NOTE :
    An non displayed element (visibility-hidden) could be added to describe accessibility informations.
    - search status: wainting for query, searching, no result found
    - result count: displaying
    e.g: <span class="visibility-hidden" aria-live="polite" aria-atomic="true">10 results found for query "test"</span>
     */

    return [form, ul]
}

function rzSearchRenderer(args: Args) {
    const wrapper = rzElement({ ...args, tag: 'rz-search' })

    const dialog = rzDialogRenderer({
        ...args.dialogData,
        innerHTML: innerDialogRenderer(args)
            .map((el) => el.outerHTML)
            .join(''),
    })
    wrapper.appendChild(dialog)

    const button = rzButtonRenderer({
        label: 'Open search',
        attributes: {
            opentarget: args.dialogData?.dialogId,
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

export const WithOpenKeyBind: Story = {
    render: (args) => {
        return rzSearchRenderer(args)
    },
    args: {
        ...meta.args,
        attributes: {
            'open-key': 'meta+k',
        },
    },
}

export const DefaultOpen: Story = {
    render: (args) => {
        return rzSearchRenderer(args)
    },
    args: {
        dialogData: {
            ...meta.args.dialogData,
            dialogId: 'search-dialog-default-open',
        },
        attributes: {
            ['initial-value']: 'test',
        },
    },
}
