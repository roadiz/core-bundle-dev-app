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

/** Past six ancestors the levels after the root fold into a popover, so a deep node tree stays on one line. */
export const Collapsed: Story = {
    args: {
        parents: [
            { label: 'All nodes', url: '#' },
            { label: 'Home', url: '#' },
            { label: 'Category', url: '#' },
            { label: 'Subcategory', url: '#' },
            { label: 'Sub-subcategory', url: '#' },
            { label: 'Deeper', url: '#' },
            { label: 'Deeper still', url: '#' },
            { label: 'Deepest', url: '#' },
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

const nextFrame = () => new Promise(requestAnimationFrame)

/**
 * Drag the resize handle: levels fold into the popover as the row narrows and come back as it
 * widens, up to the six the server left inline. The trail never wraps.
 */
export const Responsive: Story = {
    args: {
        parents: [
            { label: 'All nodes', url: '#' },
            { label: 'Home', url: '#' },
            { label: 'Category', url: '#' },
            { label: 'Subcategory', url: '#' },
            { label: 'Sub-subcategory', url: '#' },
            { label: 'Deeper', url: '#' },
        ],
    },
    decorators: [
        (story) => {
            const container = document.createElement('div')
            container.style.cssText =
                'display: flex; resize: horizontal; overflow: hidden; width: 480px; max-width: 100%; padding: 8px; border: 1px dashed #ccc;'
            container.appendChild(story() as HTMLElement)

            return container
        },
    ],
    play: async ({ canvasElement, args }) => {
        await nextFrame()
        await nextFrame()

        const trail = Array.from(
            canvasElement.querySelectorAll<HTMLElement>(
                '.rz-breadcrumb__list > .rz-breadcrumb__list-item:not(.rz-overflow-list)',
            ),
        )
        const labels = trail.map((li) => li.textContent?.trim())
        const expected = [
            args.parents[0].label,
            args.parents[args.parents.length - 1].label,
            args.current,
        ]
        for (const label of expected) {
            if (!labels.includes(label)) {
                throw new Error(`"${label}" must stay in the trail`)
            }
        }
        // The current page is not an ancestor.
        if (trail.length - 1 > 6) {
            throw new Error(
                `${trail.length - 1} ancestors on screen, 6 at most`,
            )
        }
        for (const li of trail) {
            if (li.scrollWidth > li.clientWidth + 1) {
                throw new Error(`"${li.textContent?.trim()}" is squeezed`)
            }
        }
    },
}
