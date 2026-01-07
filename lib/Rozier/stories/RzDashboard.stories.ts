import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    rzButtonRenderer,
    type RzButtonOptions,
} from '~/utils/component-renderer/rzButton'
import { rzIconRenderer } from '~/utils/component-renderer/rzIcon'

type Panel = {
    type?: 'card' | 'default'
    header?: {
        iconClass?: string
        title: string
        button?: RzButtonOptions
    }
    content: string
}

export type Args = {
    rows: Panel[][]
}

const meta: Meta<Args> = {
    title: 'Components/Dashboard',
    tags: ['autodocs'],
    args: {
        rows: [
            [
                {
                    type: 'card',
                    content: `<div class="text-h1-md">Title 1</div>
					<p>This is the content of panel 1.</p>`,
                },
                {
                    type: 'card',
                    content: `<div class="text-h1-md">Title 2</div>
					<p>This is the content of panel 2.</p>`,
                },
            ],
            [
                {
                    type: 'card',
                    content: 'This is the content of panel 1.',
                },
                {
                    type: 'card',
                    content: 'This is the content of panel 1.',
                },
                {
                    type: 'card',
                    content: 'This is the content of panel 1.',
                },
            ],
            [
                {
                    header: {
                        iconClass: 'rz-icon-ri--file-list-3-line',
                        title: 'Panel 1',
                        button: {
                            label: 'Action',
                            iconClass: 'rz-icon-ri--add-line',
                        },
                    },
                    content: 'This is the content of panel 1.',
                },
                {
                    header: {
                        iconClass: 'rz-icon-ri--file-list-3-line',
                        title: 'Panel 2',
                        button: {
                            label: 'Action',
                            iconClass: 'rz-icon-ri--add-line',
                        },
                    },
                    content: 'This is the content of panel 1.',
                },
            ],
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function rzPanelRenderer(panel: Panel) {
    const panelEl = document.createElement('div')
    panelEl.classList.add('rz-dashboard__panel')

    if (panel.type === 'card') {
        panelEl.classList.add('rz-dashboard__panel--card')
    }

    if (panel.header) {
        const headerEl = document.createElement('div')
        headerEl.classList.add('rz-dashboard__panel__header')

        if (panel.header.iconClass) {
            const iconEl = rzIconRenderer({ class: panel.header.iconClass })
            headerEl.appendChild(iconEl)
        }

        const titleEl = document.createElement('span')
        titleEl.classList.add('rz-dashboard__panel__title')
        titleEl.textContent = panel.header.title
        headerEl.appendChild(titleEl)

        if (panel.header.button) {
            const buttonEl = rzButtonRenderer(panel.header.button)
            buttonEl.classList.add('rz-dashboard__header__button')
            headerEl.appendChild(buttonEl)
        }

        panelEl.appendChild(headerEl)
    }

    const contentEl = document.createElement('div')
    contentEl.classList.add('rz-dashboard__panel__body')
    contentEl.innerHTML = panel.content
    panelEl.appendChild(contentEl)

    return panelEl
}

function rzDashboardRowRenderer(panels: Panel[]) {
    const row = document.createElement('div')
    row.classList.add('rz-dashboard__row')

    panels.forEach((panel) => {
        const panelEl = rzPanelRenderer(panel)
        row.appendChild(panelEl)
    })

    return row
}

function rzDashboardRenderer(args: Args) {
    const dashboard = document.createElement('div')
    dashboard.classList.add('rz-dashboard')

    args.rows.forEach((panels) => {
        const row = rzDashboardRowRenderer(panels)
        dashboard.appendChild(row)
    })

    return dashboard
}

export const Default: Story = {
    render: (args) => {
        return rzDashboardRenderer(args)
    },
}
