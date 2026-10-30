import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import {
    type RzPageOptions,
    rzPageRenderer,
} from '~/utils/storybook/renderer/rzPage'

type Args = RzPageOptions

const PLACEHOLDER_CONTENT = Array(12)
    .fill(
        '<div style="height:96px;margin:16px 0;border-radius:6px;background:var(--surface-light-tertiary)"></div>',
    )
    .join('')

const meta: Meta<Args> = {
    title: 'Patterns/Page',
    tags: ['autodocs'],
    render: (args) => {
        const page = rzPageRenderer(args)
        // The back-to-top button of layouts/base.html.twig is hidden: any button can send --scroll-top
        page.querySelector('#main-content-scrollable')?.appendChild(
            rzButtonRenderer({
                label: 'Scroll to top',
                iconClass: 'rz-icon-ri--arrow-up-line',
                emphasis: 'secondary',
                attributes: {
                    type: 'button',
                    command: '--scroll-top',
                    commandfor: args.id || 'rz-page',
                },
            }),
        )
        return page
    },
    args: {
        id: 'rz-page',
        content: PLACEHOLDER_CONTENT,
    },
    parameters: {
        layout: 'fullscreen',
        // One iframe per story: the instances would share the rz-page ID on the docs page
        docs: { story: { inline: false, iframeHeight: 600 } },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}
