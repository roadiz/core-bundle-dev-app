import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'

type Args = {
    open: boolean
    widths: number[]
}

const meta: Meta<Args> = {
    title: 'Components/LivePreview',
    tags: ['autodocs'],
    args: {
        open: true,
        widths: [375, 768, 1440],
    },
    argTypes: {
        open: {
            description:
                'Initial state. The element persists it in localStorage (rz-live-preview-open).',
        },
        widths: {
            description:
                'roadiz_rozier.live_preview.viewport_widths. The pressed button must match the innerWidth printed by the framed page.',
        },
    },
    parameters: {
        layout: 'fullscreen',
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

function render(args: Args) {
    try {
        localStorage.setItem('rz-live-preview-open', args.open ? '1' : '0')
        localStorage.removeItem('rz-live-preview-width')
    } catch {
        // private mode
    }

    // Same nesting as editSource.html.twig: #main-content > .content-global > header + form + preview
    const main = el('div', '')
    main.id = 'main-content'
    main.style.cssText = 'height: 100vh; overflow-y: auto; position: relative;'

    const page = el('section', 'content-global')
    const header = el('header', 'content-header')
    header.style.minHeight = '120px'
    const slot = el('div', 'content-header-action-menu')
    const nav = document.createElement('nav')
    const toggle = document.createElement('button')
    toggle.type = 'button'
    toggle.className = 'uk-button rz-live-preview-toggle'
    toggle.setAttribute('aria-controls', 'live-preview')
    toggle.setAttribute('aria-expanded', 'false')
    toggle.innerHTML =
        '<i class="uk-icon-rz-visibility"></i> <span class="label">Live preview</span>'
    nav.appendChild(toggle)
    slot.appendChild(nav)
    header.appendChild(slot)

    const form = el(
        'form',
        'content',
        Array(12)
            .fill(
                '<div style="height:96px;margin:16px;border-radius:6px;background:var(--surface-light-tertiary)"></div>',
            )
            .join(''),
    )

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
            rzButtonRenderer(
                { emphasis: 'low', size: 'sm', iconClass: iconFor(width) },
                {
                    type: 'button',
                    'data-width': String(width),
                    'aria-pressed': 'false',
                    'aria-label': `${width} px`,
                    title: `${width} px`,
                },
            ),
        )
    })
    toolbar.appendChild(group)
    toolbar.appendChild(
        el(
            'span',
            'rz-badge rz-badge--size-xs rz-live-preview__scale',
            '<span class="rz-badge__label">100 %</span>',
        ),
    )
    toolbar.appendChild(
        rzButtonRenderer(
            {
                emphasis: 'low',
                size: 'sm',
                iconClass: 'rz-icon-ri--close-line',
            },
            {
                type: 'button',
                'data-close': '',
                'aria-label': 'Close live preview',
                title: 'Close live preview',
            },
        ),
    )

    const viewport = el('div', 'rz-live-preview__viewport')
    const iframe = document.createElement('iframe')
    iframe.title = 'Live preview'
    iframe.srcdoc = FRONT_SRCDOC
    viewport.appendChild(iframe)
    preview.append(toolbar, viewport)

    page.append(header, form, preview)
    main.appendChild(page)

    return main
}

export const Open: Story = { render }

export const Closed: Story = {
    render,
    args: { open: false },
}

export const SingleViewport: Story = {
    render,
    args: { widths: [1024] },
}
