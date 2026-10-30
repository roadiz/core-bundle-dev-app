import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzActionsMenuRenderer } from '~/utils/storybook/renderer/rzActionsMenu'
import {
    type RzSaveButtonOptions,
    rzSaveButtonRenderer,
} from '~/utils/storybook/renderer/rzSaveButton'

type Args = RzSaveButtonOptions

const FORM_ID = 'rz-save-button-story-form'

const meta: Meta<Args> = {
    title: 'Components/Actions/SaveButton',
    tags: ['autodocs'],
    render: (args) => {
        const wrapper = document.createElement('div')

        const form = document.createElement('form')
        form.id = FORM_ID
        form.innerHTML = `<label>Title <input is="rz-input" type="text" name="title" value="My node"></label>
<p><output></output></p>`
        form.addEventListener('submit', (event) => {
            event.preventDefault()
            form.querySelector('output')!.textContent =
                `Submitted at ${new Date().toLocaleTimeString()}`
        })
        wrapper.appendChild(form)

        // The button moves itself into the first .rz-actions-menu of the document
        wrapper.appendChild(rzActionsMenuRenderer({ items: [] }))

        wrapper.appendChild(rzSaveButtonRenderer(args))

        return wrapper
    },
    args: {
        dataActionSave: `#${FORM_ID}`,
        label: 'Save',
    },
    argTypes: {
        dataActionSave: {
            description:
                'Selector of the form submitted on click and on Cmd/Ctrl+S.',
        },
    },
    parameters: {
        // One iframe per story: the button binds to the first actions menu of the document
        docs: { story: { inline: false, iframeHeight: 300 } },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}
