import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import {
    type RzPagePreviewOptions,
    rzPagePreviewRenderer,
} from '~/utils/storybook/renderer/rzPagePreview'

type Args = RzPagePreviewOptions

const TEXT_CONTENT = `
    <p class="text-body-md">The page preview layout shows a simple standalone page with the backoffice styles, outside the backoffice shell: a title, an optional description and note, the session flash messages, then any content.</p>
    <p class="text-body-md">Use it for public-facing pages that only need a readable column, such as a form or a confirmation.</p>
`

function actions(button: HTMLElement) {
    const actions = document.createElement('div')
    actions.classList.add('rz-page-preview__actions')
    actions.appendChild(button)
    return actions
}

/** Usage example: the public custom form page (RoadizCoreBundle customForm/customForm.html.twig) */
function formContent() {
    const form = document.createElement('form')
    form.classList.add('rz-form')
    form.appendChild(
        rzFormFieldRenderer({
            label: 'Email',
            required: true,
            error: 'This value should not be blank.',
            input: {
                type: 'email',
                name: 'email',
                id: 'page-preview-email',
                placeholder: 'name@example.com',
            },
        }),
    )
    form.appendChild(
        rzFormFieldRenderer({
            label: 'Message',
            input: {
                type: 'text',
                name: 'message',
                id: 'page-preview-message',
            },
        }),
    )
    form.appendChild(
        actions(
            rzButtonRenderer({
                label: 'Submit',
                emphasis: 'primary',
                iconClass: 'rz-icon-ri--mail-send-line',
                attributes: { type: 'submit' },
            }),
        ),
    )

    // HTML, so that each story renders its own copy
    return form.outerHTML
}

const meta: Meta<Args> = {
    title: 'Patterns/PagePreview',
    tags: ['autodocs'],
    render: (args) => rzPagePreviewRenderer(args),
    args: {
        title: 'Page title',
        description: '<p>Optional description, rendered from Markdown.</p>',
        content: TEXT_CONTENT,
    },
    parameters: {
        layout: 'fullscreen',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const TitleOnly: Story = {
    args: {
        description: undefined,
    },
}

export const WithFlashMessages: Story = {
    args: {
        messages: [
            {
                text: 'Your changes have been saved.',
                color: 'success',
                attributes: { role: 'status' },
            },
            {
                text: 'This page will be unpublished tomorrow.',
                color: 'warning',
                attributes: { role: 'alert' },
            },
        ],
    },
}

export const FormExample: Story = {
    args: {
        title: 'Contact',
        note: '* Required fields',
        content: formContent(),
    },
}
