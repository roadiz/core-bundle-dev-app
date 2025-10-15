import type { Meta, StoryObj } from '@storybook/html-vite'
import '../../app/assets/css/text.css'

type nodeAttribute = {
    tag: string
    class: string
    content: string
}

const nodeList = {
    h1Medium: {
        tag: 'div',
        class: 'text-h1-md',
        content: 'Title H1 MD',
    },
    h1Small: {
        tag: 'div',
        class: 'text-h1-sm',
        content: 'Title H1 SM',
    },
    overtitleSmall: {
        tag: 'div',
        class: 'text-overtitle-sm',
        content: 'Overtitle Small',
    },
    overtitleXs: {
        tag: 'div',
        class: 'text-overtitle-xs',
        content: 'Overtitle XS',
    },
    overtitleMd: {
        tag: 'div',
        class: 'text-overtitle-md',
        content: 'Overtitle MD',
    },
    labelMd: {
        tag: 'div',
        class: 'text-label-md',
        content: 'Label MD',
    },
    labelSm: {
        tag: 'div',
        class: 'text-label-sm',
        content: 'Label SM',
    },
    labelXs: {
        tag: 'div',
        class: 'text-label-xs',
        content: 'Label XS',
    },
    searchMd: {
        tag: 'div',
        class: 'text-search-md',
        content: 'Search MD',
    },
    tabMd: {
        tag: 'div',
        class: 'text-tab-md',
        content: 'Tab MD',
    },
    tabSm: {
        tag: 'div',
        class: 'text-tab-sm',
        content: 'Tab SM',
    },
    guideMd: {
        tag: 'div',
        class: 'text-guide-md',
        content: 'Guide MD',
    },
    listMd: {
        tag: 'div',
        class: 'text-list-md',
        content: 'List MD',
    },
    listSm: {
        tag: 'div',
        class: 'text-list-sm',
        content: 'List SM',
    },
    nodeMd: {
        tag: 'div',
        class: 'text-node-md',
        content: 'Node MD',
    },
    calendarMonth: {
        tag: 'div',
        class: 'text-calendar-month',
        content: 'Calendar Month',
    },
    calendarDay: {
        tag: 'div',
        class: 'text-calendar-day',
        content: 'Calendar Day',
    },
    calendarDate: {
        tag: 'div',
        class: 'text-calendar-date',
        content: 'Calendar Date',
    },
    breadcrumbMd: {
        tag: 'div',
        class: 'text-breadcrumb-md',
        content: 'Breadcrumb MD',
    },
    breadcrumbSm: {
        tag: 'div',
        class: 'text-breadcrumb-sm',
        content: 'Breadcrumb SM',
    },
    tooltipMd: {
        tag: 'div',
        class: 'text-tooltip-md',
        content: 'Tooltip MD',
    },
    tooltipSm: {
        tag: 'div',
        class: 'text-tooltip-sm',
        content: 'Tooltip SM',
    },
    tagMd: {
        tag: 'span',
        class: 'text-tag-md',
        content: 'Tag MD',
    },
    tagSm: {
        tag: 'span',
        class: 'text-tag-sm',
        content: 'Tag SM',
    },
    cardMd: {
        tag: 'div',
        class: 'text-card-md',
        content: 'Card MD',
    },
    cardSm: {
        tag: 'div',
        class: 'text-card-sm',
        content: 'Card SM',
    },
    titleMd: {
        tag: 'div',
        class: 'text-title-md',
        content: 'Title MD',
    },
    toastTitle: {
        tag: 'div',
        class: 'text-toast-title',
        content: 'Toast Title',
    },
    toastText: {
        tag: 'div',
        class: 'text-toast-text',
        content: 'Toast Text',
    },
    legendMd: {
        tag: 'div',
        class: 'text-legend-md',
        content: 'Legend MD',
    },
    formLabel: {
        tag: 'div',
        class: 'text-form-label',
        content: 'Form Label',
    },
    formInputText: {
        tag: 'div',
        class: 'text-form-input-text',
        content: 'Form Input Text',
    },
    formPlaceholder: {
        tag: 'div',
        class: 'text-form-placeholder',
        content: 'Form Placeholder',
    },
    formSupportingText: {
        tag: 'div',
        class: 'text-form-supporting-text',
        content: 'Form Supporting Text',
    },
    formMarkdown: {
        tag: 'div',
        class: 'text-form-markdown',
        content: 'Form Markdown',
    },
    formMarkdownQuickView: {
        tag: 'div',
        class: 'text-form-markdown-quick-view',
        content: 'Form Markdown Quick View',
    },
    formDescriptionMd: {
        tag: 'div',
        class: 'text-form-description-md',
        content: 'Form Description MD',
    },
    formDescriptionSm: {
        tag: 'div',
        class: 'text-form-description-sm',
        content: 'Form Description SM',
    },
    formDescriptionXs: {
        tag: 'div',
        class: 'text-form-description-xs',
        content: 'Form Description XS',
    },
} as Record<string, nodeAttribute>

const meta: Meta = {
    title: 'Integration/Text',
}

export default meta
type Story = StoryObj

function elementRenderer(attribute: nodeAttribute) {
    const node = document.createElement(attribute.tag)
    node.innerText = attribute.content
    if (attribute.class) node.classList.add(attribute.class)
    return node
}

export const Primary: Story = {
    render: () => {
        const container = document.createElement('div')
        container.classList.add('story-container')

        Object.entries(nodeList).forEach((entry) => {
            const nodeData = entry[1]
            const item = document.createElement('div')
            item.style.marginBlock = '60px'
            container.appendChild(item)

            const classLabel = elementRenderer({
                tag: 'span',
                class: '',
                content: `.${nodeData.class}`,
            })
            classLabel.style = `float: right;`
            item.appendChild(classLabel)

            const node = elementRenderer(nodeData)
            item.appendChild(node)

            const hr = document.createElement('hr')
            item.appendChild(hr)

            const paragraph = document.createElement('div')
            paragraph.innerText =
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
            paragraph.classList.add(nodeData.class)
            item.appendChild(paragraph)
        })

        return container
    },
    args: {},
}
