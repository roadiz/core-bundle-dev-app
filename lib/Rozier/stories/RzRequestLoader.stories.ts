import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzPageRenderer } from '~/utils/storybook/renderer/rzPage'
import {
    type RzRequestLoaderOptions,
    rzRequestLoaderRenderer,
} from '~/utils/storybook/renderer/rzRequestLoader'

type Args = RzRequestLoaderOptions

const PLACEHOLDER_CONTENT = Array(12)
    .fill(
        '<div style="height:96px;margin:16px 0;border-radius:6px;background:var(--surface-light-tertiary)"></div>',
    )
    .join('')

function withPage(loader: HTMLElement, content: string = PLACEHOLDER_CONTENT) {
    const page = rzPageRenderer({ content })
    page.prepend(loader)
    return page
}

const meta: Meta<Args> = {
    title: 'Components/Feedback/RequestLoader',
    tags: ['autodocs'],
    // Rendered as the first child of the page, as in ui/layouts/base.html.twig
    render: (args) => withPage(rzRequestLoaderRenderer(args)),
    args: {
        label: 'Loading',
        active: true,
    },
    argTypes: {
        active: {
            description:
                'Initial state, shown at once. At runtime requestLoaderShow adds rz-request-loader--active (bar), then rz-request-loader--slow (pill, announcement) after 500 ms; requestLoaderHide removes both, once the pill has stayed at least 600 ms.',
        },
    },
    parameters: {
        layout: 'fullscreen',
        // The bar is fixed on top of the viewport and the page uses the rz-page ID: one iframe per story
        docs: { story: { inline: false, iframeHeight: 400 } },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

/**
 * Any module shows or hides the loader by dispatching
 * `requestLoaderShow` / `requestLoaderHide` on `window`.
 */
export const DrivenByEvents: Story = {
    args: {
        active: false,
    },
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.style.cssText =
            'display:flex;gap:8px;padding:64px 0 16px;flex-wrap:wrap'
        ;[
            ['Show loader', 'requestLoaderShow'],
            ['Hide loader', 'requestLoaderHide'],
        ].forEach(([label, eventName]) => {
            wrapper.appendChild(
                rzButtonRenderer({
                    label,
                    emphasis: 'secondary',
                    attributes: { type: 'button' },
                    on: {
                        click: () =>
                            window.dispatchEvent(new CustomEvent(eventName)),
                    },
                }),
            )
        })
        const page = withPage(rzRequestLoaderRenderer(args), '')
        page.querySelector('#main-content-scrollable')?.prepend(wrapper)
        return page
    },
}
