import type { Meta, StoryObj } from '@storybook/html-vite'

type HeaderElement = {
    tag: string
    innerText?: string
    innerHTML?: string
    className?: string
    attributes?: Record<string, string>
    children?: HeaderElement[]
}

const ITEM_CLASSES = [
    'rz-page-header__item',
    'rz-page-header__item-grow',
    'rz-page-header__item-right',
]

const COMPONENT_CLASS_NAME = 'rz-page-header'
export type Args = {
    rows: HeaderElement[][]
    itemClass?: string
}

const meta: Meta<Args> = {
    title: 'Components/PageHeader',
    tags: ['autodocs'],
    args: {
        rows: [
            [
                {
                    tag: 'select',
                    attributes: {
                        name: 'lang',
                        id: 'lang',
                    },
                    children: [
                        {
                            tag: 'option',
                            innerText: 'EN',
                            attributes: { value: 'en', selected: 'selected' },
                        },
                        {
                            tag: 'option',
                            innerText: 'FR',
                            attributes: { value: 'fr' },
                        },
                        {
                            tag: 'option',
                            innerText: 'DE',
                            attributes: { value: 'de' },
                        },
                    ],
                },
                {
                    tag: 'nav',
                    className: 'rz-page-header__item-grow',
                    children: [
                        {
                            tag: 'ol',
                            children: [
                                {
                                    tag: 'li',
                                    innerText: 'Home',
                                },
                                {
                                    tag: 'li',
                                    innerText: 'Section',
                                },
                                {
                                    tag: 'li',
                                    innerText: 'Section',
                                },
                            ],
                        },
                    ],
                },
                {
                    tag: 'button',
                    className: 'rz-button',
                    innerHTML:
                        '<span class="rz-button__label">button label</span><span class="rz-button__icon rz-icon-ri--arrow-drop-right-line"></span>',
                },
            ],
            [
                {
                    tag: 'h1',
                    innerText: 'Page Title',
                    className: 'rz-page-header__title',
                },
                {
                    tag: 'div',
                    className: 'rz-badge',
                    innerHTML:
                        '<span class="rz-badge__icon rz-icon-ri--information-line"></span>',
                },
                {
                    tag: 'div',
                    className:
                        'rz-page-header__item-right rz-badge rz-badge--warning',
                    innerHTML:
                        '<span class="rz-badge__icon rz-icon-rz--status-draft-fill"></span><span class="rz-badge__label">Draft</span>',
                },
            ],
        ],
    },
    argTypes: {
        itemClass: {
            control: { type: 'select' },
            options: ITEM_CLASSES,
        },
    },
}

export default meta
type Story = StoryObj<Args>

function elementRenderer(args: HeaderElement) {
    const el = document.createElement(args.tag || 'div')
    if (args.className) el.className = args.className
    el.classList.add(COMPONENT_CLASS_NAME + '__item')

    if (args.innerHTML) {
        el.innerHTML = args.innerHTML
        return el
    }

    el.innerText = args.innerText || ''

    if (args.attributes) {
        Object.entries(args.attributes).forEach(([key, value]) => {
            el.setAttribute(key, value)
        })
    }

    if (args.children) {
        args.children.forEach((child) => {
            const childEl = elementRenderer(child)
            el.appendChild(childEl)
        })
    }

    return el
}

function rowRenderer(args: Args['rows'][number]) {
    const wrapper = document.createElement('div')
    wrapper.className = COMPONENT_CLASS_NAME + '__row'

    args.forEach((element) => {
        const el = elementRenderer(element)
        wrapper.appendChild(el)
    })

    return wrapper
}

function rzPageHeaderRenderer(args: Args) {
    const wrapper = document.createElement('header')
    wrapper.className = COMPONENT_CLASS_NAME

    args.rows.forEach((row) => {
        const rowNode = rowRenderer(row)
        wrapper.appendChild(rowNode)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzPageHeaderRenderer(args)
    },
}

export const Item: Story = {
    render: (args) => {
        args.rows.forEach((row) => {
            return row.map((el) => {
                el.className = args.itemClass || ''
            })
        })
        return rzPageHeaderRenderer(args)
    },
    args: {
        rows: [
            [
                {
                    tag: 'div',
                    innerText: 'Single Item',
                    attributes: {
                        style: 'background-color: #e0e0e0; padding: 1rem;',
                    },
                },
            ],
        ],
        itemClass: ITEM_CLASSES[0],
    },
}
