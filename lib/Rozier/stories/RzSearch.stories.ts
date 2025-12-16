import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzDialogRenderer } from '~/utils/storybook/renderer/rzDialog'
import { rzInputRenderer } from '~/utils/storybook/renderer/rzInput'
import type { Args as DialogArgs } from './RzDialog.stories'
import {
    rzButtonRenderer,
    type RzButtonOptions,
} from '~/utils/component-renderer/rzButton'
import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

export type Args = RzElement & {
    value?: string
    action?: string
    placeholder?: string
    dialogData: DialogArgs & Required<Pick<DialogArgs, 'dialogId'>>
    buttonOptions?: RzButtonOptions
}

const COMPONENT_CLASS_NAME = 'rz-search'

const statusTexts = {
    'idle-text': 'Waiting for request',
    'reset-text': 'Request reset',
    'pending-text': 'Searching...',
    'unique-result-text': `1 result found`,
    'results-text': `{n} results found`,
    'no-results-text': 'No results found',
    'error-text':
        'An error occurred while fetching results. Please check your network connection and try again.',
}

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
                        tag: 'a',
                        is: '',
                        label: 'Advanced search',
                        emphasis: 'primary',
                        attributes: {
                            href: '/search',
                            class: 'rz-dialog__item--end',
                        },
                    },
                ],
            },
        },
        attributes: { ...statusTexts },
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
    form.setAttribute('aria-label', 'An entity')

    const searchInput = rzInputRenderer({
        type: 'search',
        name: 'searchTerms',
        id: 'nodes-sources-search-input',
        placeholder: args.placeholder,
        value: args.value || '',
    })
    form.appendChild(searchInput)

    /* Use to display search status message and spinner */
    const statusWrapper = document.createElement('div')
    statusWrapper.setAttribute('status-wrapper', '')
    statusWrapper.classList.add(`${COMPONENT_CLASS_NAME}__status-wrapper`)

    const ul = document.createElement('ul')
    ul.setAttribute('results-wrapper', '')
    ul.classList.add(`${COMPONENT_CLASS_NAME}__list`)

    return [form, statusWrapper, ul]
}

function rzSearchRenderer(args: Args) {
    const wrapper = rzElement({ ...args, tag: 'rz-search' })

    const button = rzButtonRenderer({
        label: 'Open search',
        ...args.buttonOptions,
        attributes: {
            opentarget: args.dialogData?.dialogId,
            ...(args.buttonOptions?.attributes || {}),
        },
    })
    wrapper.appendChild(button)

    const dialog = rzDialogRenderer({
        ...args.dialogData,
        innerHTML: innerDialogRenderer(args)
            .map((el) => el.outerHTML)
            .join(''),
    })
    wrapper.appendChild(dialog)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzSearchRenderer(args)
    },
    args: {
        dialogData: {
            ...meta.args.dialogData,
            dialogId: 'Default-2',
        },
    },
}

export const WithOpenKeyBind: Story = {
    render: (args) => {
        return rzSearchRenderer(args)
    },
    args: {
        ...meta.args,
        buttonOptions: {
            label: 'Open search with Meta+K',
        },
        attributes: {
            'open-key': 'meta+k',
        },
        dialogData: {
            ...meta.args.dialogData,
            dialogId: 'WithOpenKeyBind',
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
            'initial-value': 'test',
        },
    },
}
