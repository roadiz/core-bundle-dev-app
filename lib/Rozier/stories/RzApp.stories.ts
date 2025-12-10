import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    DEFAULT_NAV_ITEMS,
    rzHeaderRenderer,
} from '~/utils/storybook/renderer/rzHeader'

export type Args = {
    hasTreesSection: boolean
}

const ELEMENT_CLASS_NAME = 'rz-app'
const meta: Meta<Args> = {
    title: 'Integration/Layout/App',
    tags: ['autodocs'],
    args: {
        hasTreesSection: true,
    },
    parameters: {
        layout: 'fullscreen',
    },
}

export default meta
type Story = StoryObj<Args>

function appContainerRenderer(args: Args) {
    const app = document.createElement('div')
    app.classList.add(ELEMENT_CLASS_NAME)

    const header = rzHeaderRenderer({ navItems: DEFAULT_NAV_ITEMS })
    app.appendChild(header)

    if (args.hasTreesSection) {
        const treesSection = document.createElement('section')
        treesSection.textContent = 'Trees Section'
        treesSection.classList.add('rz-trees-section')
        app.appendChild(treesSection)
    }

    const main = document.createElement('main')
    const innerMain = document.createElement('div')
    innerMain.style.minHeight = '200vh'
    main.appendChild(innerMain)
    innerMain.textContent = 'Main Content'
    main.classList.add('rz-page')
    app.appendChild(main)

    return { app, header }
}

export const Default: Story = {
    render: (args) => {
        return appContainerRenderer(args).app
    },
}
