import type { Meta, StoryObj } from '@storybook/html-vite'

type nodeAttribute = {
    tag: string
    class: string
    content: string
}

const nodeList = {
    h1Medium: {
        tag: 'h1',
        class: 'title-h1-md',
        content: 'this is my title MD',
    },
    h1Small: {
        tag: 'h1',
        class: 'title-h1-sm',
        content: 'this is my title SM',
    },
} as Record<string, nodeAttribute>

const meta: Meta = {
    title: 'Integration/Typography',
    argTypes: {},
    tags: ['autodocs'],
}

export default meta
type Story = StoryObj

function elementRenderer(attribute: nodeAttribute) {
    const node = document.createElement(attribute.tag)
    node.innerText = attribute.content
    node.classList.add(attribute.class)
    return node
}

export const Primary: Story = {
    render: () => {
        const container = document.createElement('div')

        Object.entries(nodeList).forEach(([name, nodeData]) => {
            const title = document.createElement('div')
            title.innerText = name
            container.appendChild(title)

            const node = elementRenderer(nodeData)
            container.appendChild(node)
        })

        return container
    },
    args: {},
}
