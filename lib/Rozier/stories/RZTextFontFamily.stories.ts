import type { Meta, StoryObj } from '@storybook/html-vite'

type nodeAttribute = {
    tag: string
    class: string
    innerText?: string
}

const nodeList = {
    title: {
        tag: 'span',
        class: 'font-title',
    },
    text: {
        tag: 'span',
        class: 'font-text',
    },
    label: {
        tag: 'span',
        class: 'font-label',
    },
    markdown: {
        tag: 'span',
        class: 'font-markdown',
    },
    code: {
        tag: 'span',
        class: 'font-code',
    },
} as Record<string, nodeAttribute>

const meta: Meta = {
    title: 'Integration/Text/font-family',
}

export default meta
type Story = StoryObj

function elementRenderer(attribute: nodeAttribute) {
    const node = document.createElement(attribute.tag)
    node.innerText = attribute.innerText
    if (attribute.class) node.classList.add(attribute.class)
    return node
}

function headerRenderer(cols: string[]) {
    const thead = document.createElement('thead')

    const tr = document.createElement('tr')
    thead.appendChild(tr)

    cols.forEach((col) => {
        const th = document.createElement('th')
        th.scope = 'col'
        th.innerText = col
        tr.appendChild(th)
    })

    return thead
}

export const Default: Story = {
    render: () => {
        const table = document.createElement('table')
        const header = headerRenderer(['Class name', 'Name'])
        table.appendChild(header)

        const body = document.createElement('tbody')
        table.appendChild(body)

        Object.entries(nodeList).forEach(([key, values]) => {
            const tr = document.createElement('tr')
            body.appendChild(tr)

            const classNameEL = elementRenderer({
                ...values,
                innerText: '.' + values.class,
                tag: 'td',
            })

            tr.appendChild(classNameEL)

            const nameEl = elementRenderer({
                ...values,
                innerText: key,
                tag: 'td',
            })
            nameEl.style.paddingInline = '20px'
            tr.appendChild(nameEl)
        })
        return table
    },
}
