import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzActionsMenuRenderer } from '~/utils/storybook/renderer/rzActionsMenu'

type Args = {
    open: boolean
    widths: number[]
}

const meta: Meta<Args> = {
    title: 'Patterns/LivePreview',
    tags: ['autodocs'],
    render: (args) => rzLivePreviewRenderer(args),
    args: {
        open: true,
        widths: [375, 768, 1440],
    },
    argTypes: {
        open: {
            description:
                'Initial state. The element persists it in localStorage (rz-live-preview-open). The preview and its toggle are hidden below 960px (--screen-md-min): widen the canvas to see them.',
        },
        widths: {
            description:
                'roadiz_rozier.live_preview.viewport_widths. The pressed button must match the innerWidth printed by the framed page.',
        },
    },
    parameters: {
        layout: 'fullscreen',
        // One iframe per story: the fixed actions menus would stack on the docs page.
        docs: { story: { inline: false, iframeHeight: 600 } },
    },
}

export default meta
type Story = StoryObj<Args>

/* Stand-in for the front: prints the viewport width it is rendered at. */
const FRONT_SRCDOC = `<!doctype html>
<body style="margin:0;font:600 48px/1.2 system-ui;display:grid;place-items:center;height:100vh;background:#fff;color:#18191b">
<script>document.body.textContent = window.innerWidth + ' px'</script>
</body>`

function iconFor(width: number) {
    if (width < 600) return 'rz-icon-ri--smartphone-line'
    if (width < 1024) return 'rz-icon-ri--tablet-line'
    return 'rz-icon-ri--computer-line'
}

function el(tag: string, className: string, innerHTML = '') {
    const node = document.createElement(tag)
    node.className = className
    node.innerHTML = innerHTML
    return node
}

function rzLivePreviewRenderer(args: Args) {
    try {
        localStorage.setItem('rz-live-preview-open', args.open ? '1' : '0')
        localStorage.removeItem('rz-live-preview-width')
    } catch {
        // private mode
    }

    // Same nesting as base.html.twig + nodes/editSource.html.twig: .rz-page > .rz-page__body > form + preview
    const page = el('main', 'rz-page')
    const body = el('section', 'rz-page__body')
    const form = el(
        'form',
        '',
        Array(12)
            .fill(
                '<div style="height:96px;margin:16px 0;border-radius:6px;background:var(--surface-light-tertiary)"></div>',
            )
            .join(''),
    )

    // Same toggle as nodes/actionsMenu.html.twig, in the fixed actions menu.
    // The save item keeps the menu visible below 960px, where the toggle is hidden.
    const actionsMenu = rzActionsMenuRenderer({
        vertical: true,
        class: 'actions-menu',
        items: [
            {
                button: {
                    emphasis: 'primary',
                    size: 'md',
                    onDark: true,
                    iconClass: 'rz-icon-ri--eye-line',
                    attributes: {
                        type: 'button',
                        class: 'rz-live-preview-toggle',
                        title: 'Live preview',
                        'aria-label': 'Live preview',
                        'aria-controls': 'live-preview',
                        'aria-expanded': 'false',
                    },
                },
            },
            {
                button: {
                    emphasis: 'primary',
                    size: 'md',
                    onDark: true,
                    color: 'success',
                    iconClass: 'rz-icon-ri--save-line',
                    attributes: {
                        type: 'button',
                        title: 'Save',
                        'aria-label': 'Save',
                    },
                },
            },
        ],
    })

    const preview = el('rz-live-preview', 'rz-live-preview')
    preview.id = 'live-preview'
    preview.dataset.src = 'about:blank'
    preview.dataset.frontOrigin = 'https://front.example'

    const toolbar = el('div', 'rz-live-preview__toolbar')
    const group = el('div', 'rz-button-group')
    group.setAttribute('role', 'group')
    group.setAttribute('aria-label', 'Preview viewport')
    args.widths.forEach((width) => {
        group.appendChild(
            rzButtonRenderer({
                emphasis: 'tertiary',
                size: 'sm',
                iconClass: iconFor(width),
                attributes: {
                    type: 'button',
                    'data-width': String(width),
                    'aria-pressed': 'false',
                    'aria-label': `${width} px`,
                    title: `${width} px`,
                },
            }),
        )
    })
    toolbar.appendChild(group)
    toolbar.appendChild(
        el(
            'span',
            'rz-badge rz-badge--xs rz-live-preview__scale',
            '<span class="rz-badge__label">100 %</span>',
        ),
    )
    toolbar.appendChild(
        rzButtonRenderer({
            emphasis: 'tertiary',
            size: 'sm',
            iconClass: 'rz-icon-ri--close-line',
            attributes: {
                type: 'button',
                'data-close': '',
                'aria-label': 'Close live preview',
                title: 'Close live preview',
            },
        }),
    )

    const viewport = el('div', 'rz-live-preview__viewport')
    const iframe = document.createElement('iframe')
    iframe.title = 'Live preview'
    iframe.srcdoc = FRONT_SRCDOC
    viewport.appendChild(iframe)
    preview.append(toolbar, viewport)

    body.append(form, preview)
    page.appendChild(body)

    const wrapper = el('div', '')
    wrapper.append(page, actionsMenu)

    return wrapper
}

export const Default: Story = {}

export const Closed: Story = {
    args: { open: false },
}

export const SingleViewport: Story = {
    args: { widths: [1024] },
}
