import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import {
    type RzRequestLoaderOptions,
    rzRequestLoaderRenderer,
} from '~/utils/component-renderer/rzRequestLoader'

type Args = RzRequestLoaderOptions

const meta: Meta<Args> = {
    title: 'Components/Feedback/RequestLoader',
    tags: ['autodocs'],
    render: (args) => rzRequestLoaderRenderer(args),
    args: {
        label: 'Loading',
        active: true,
    },
    argTypes: {
        active: {
            description:
                'Initial state. At runtime the requestLoaderShow / requestLoaderHide window events toggle rz-request-loader--active.',
        },
    },
    parameters: {
        layout: 'fullscreen',
        // The loader is fixed on top of the viewport: one iframe per story
        docs: { story: { inline: false, iframeHeight: 120 } },
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
            'display:flex;gap:8px;padding:24px 16px 16px;flex-wrap:wrap'
        wrapper.appendChild(rzRequestLoaderRenderer(args))
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
        return wrapper
    },
}
