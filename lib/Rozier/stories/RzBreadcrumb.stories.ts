import type { Meta, StoryObj } from '@storybook/html-vite'

import {
    type BreadcrumbArgs,
    rzBreadcrumbRenderer,
} from '~/utils/storybook/renderer/rzBreadcrumb'

export type Args = BreadcrumbArgs

const meta: Meta<Args> = {
    title: 'Components/Breadcrumb',
    tags: ['autodocs'],
    render: (args) => rzBreadcrumbRenderer(args),
    args: {
        ariaLabel: 'Breadcrumb',
        overflowLabel: 'Show hidden breadcrumb levels',
        parents: [
            { label: 'All nodes', url: '#' },
            { label: 'Home', url: '#' },
            { label: 'Category', url: '#' },
        ],
        current: 'Current page',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

/** Past three ancestors the middle levels fold into a popover, so a deep node tree stays on one line. */
export const Collapsed: Story = {
    args: {
        parents: [
            { label: 'All nodes', url: '#' },
            { label: 'Home', url: '#' },
            { label: 'Category', url: '#' },
            { label: 'Subcategory', url: '#' },
            { label: 'Sub-subcategory', url: '#' },
        ],
    },
}

/** Server-side truncation caps each label, the trail still has to survive a narrow viewport. */
export const LongLabels: Story = {
    args: {
        parents: [
            { label: 'All nodes', url: '#' },
            { label: 'A page whose title goes on and on and on […]', url: '#' },
        ],
        current: 'Another remarkably long page title […]',
    },
}
