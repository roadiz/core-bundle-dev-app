import type { Meta, StoryObj } from '@storybook/html-vite'

export type Args = {
    hasTreesSection: boolean
}

const ELEMENT_CLASS_NAME = 'app-container'
const meta: Meta<Args> = {
    title: 'Integration/Layout/AppContainer',
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

    const header = document.createElement('header')
    header.textContent = 'Header'
    header.classList.add('rz-header')
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

    return app
}

export const Default: Story = {
    render: (args) => {
        return appContainerRenderer(args)
    },
}
