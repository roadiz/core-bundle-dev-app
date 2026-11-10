import type { Meta, StoryObj } from '@storybook/html-vite'
import { DEFAULT_BULK_ACTIONS } from '~/utils/storybook/renderer/rzBulkActions'
import {
    type RzBulkSectionOptions,
    rzBulkSectionRenderer,
} from '~/utils/storybook/renderer/rzBulkSection'

type Args = RzBulkSectionOptions

const meta: Meta<Args> = {
    title: 'Components/Actions/BulkSection',
    tags: ['autodocs'],
    render: (args) => {
        const section = rzBulkSectionRenderer(args)
        // Show the JSON the element writes into the hidden input instead of submitting
        const output = document.createElement('output')
        section.addEventListener('submit', (event) => {
            event.preventDefault()
            const input = (event.target as HTMLFormElement).querySelector(
                'input.bulk-form-value',
            ) as HTMLInputElement | null
            output.textContent = `Submitted: ${input?.value || '(empty)'}`
        })
        section.appendChild(output)
        return section
    },
    args: {
        selectAllButton: true,
        bulkActions: DEFAULT_BULK_ACTIONS,
        items: [
            { value: '1', label: 'Home' },
            { value: '2', label: 'About us' },
            { value: '3', label: 'Contact' },
            { value: '4', label: 'Blog' },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const WithoutSelectAllButton: Story = {
    args: {
        selectAllButton: false,
    },
}
