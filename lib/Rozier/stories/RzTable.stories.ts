import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzBadgeRenderer } from '~/utils/component-renderer/rzBadge'
import { RzElement, rzElement } from '~/utils/component-renderer/rzElement'
import { rzIconRenderer } from '~/utils/component-renderer/rzIcon'

type CellElement = {
    name: string
    ordered?: RzElement
    element: RzElement
}

type RowElement = CellElement[]

type Args = RzElement & {
    caption?: string
    header?: boolean
    body: RowElement[]
    headerDirection?: 'vertical' | 'horizontal'
    bordered?: boolean
}

const meta: Meta<Args> = {
    title: 'Components/Table',
    tags: ['autodocs'],
    args: {
        caption: 'This is the table caption',
        header: true,
        headerDirection: 'horizontal',
        bordered: false,
        body: [
            [
                {
                    name: 'id',
                    element: {
                        innerText: '1',
                    },
                },
                {
                    name: 'name',
                    ordered: {
                        tag: 'a',
                        is: 'rz-link',
                        attributes: {
                            'tooltip-text': 'Order by Name',
                            href: '?field=name&ordering=DESC',
                        },
                    },
                    element: {
                        innerText: 'Name 1',
                    },
                },
                {
                    name: 'date',
                    ordered: {
                        tag: 'a',
                        is: 'rz-link',
                        attributes: {
                            'tooltip-text': 'Order by Date',
                            href: '?field=date&ordering=ASC',
                            class: 'rz-table__ordering-link--active',
                            selected: 'true',
                        },
                    },
                    element: {
                        innerText: new Date().toISOString(),
                    },
                },
                {
                    name: 'Status',
                    element: {
                        innerHTML: rzBadgeRenderer({
                            label: 'Published',
                            color: 'success',
                            iconClass: 'rz-icon-rz-published-line',
                        }).outerHTML,
                    },
                },
            ],
            [
                {
                    name: 'id',
                    element: {
                        innerText: '2',
                    },
                },
                {
                    name: 'name',
                    element: {
                        innerText: 'Name 2',
                    },
                },
                {
                    name: 'date',
                    element: {
                        innerText: new Date().toISOString(),
                    },
                },
                {
                    name: 'Status',
                    element: {
                        innerHTML: rzBadgeRenderer({
                            label: 'Draft',
                            color: 'warning',
                            iconClass: 'rz-icon-rz-draft-line',
                        }).outerHTML,
                    },
                },
            ],
        ],
    },
    argTypes: {
        headerDirection: {
            control: { type: 'radio' },
            options: ['vertical', 'horizontal'],
        },
    },
}

export default meta
type Story = StoryObj<Args>

type RowOptions = {
    displayHeadName: boolean
    type: 'full' | 'first'
}

function rzCellRenderer(cell: CellElement, headName?: string) {
    const label = headName || cell.element.innerText
    const cellNode = rzElement({
        ...cell.element,
        tag: headName ? 'th' : 'td',
        innerText: label,
        innerHTML: headName ? undefined : cell.element.innerHTML,
    })

    if (headName && cell.ordered) {
        const iconName = cell.ordered.attributes['selected']
            ? 'rz-icon-ri--arrow-up-s-fill'
            : 'rz-icon-ri--arrow-down-s-fill'
        const link = rzElement({
            tag: 'a',
            is: 'rz-link',
            ...cell.ordered,
            innerHTML:
                headName +
                ' ' +
                rzIconRenderer({
                    class: iconName,
                }).outerHTML,
        })
        link.classList.add('rz-table__ordering-link')
        cellNode.innerHTML = link.outerHTML
    }

    return cellNode
}

function rowRenderer(rowElement: RowElement, options?: RowOptions) {
    const row = rzElement({ tag: 'tr' })

    rowElement.forEach((cellElement, index) => {
        const isHeadCell =
            options?.displayHeadName &&
            (options.type === 'first' ? index === 0 : true)
        const cell = rzCellRenderer(
            cellElement,
            isHeadCell ? cellElement.name : undefined,
        )
        row.appendChild(cell)
    })
    return row
}

function rzTableRenderer(args: Args) {
    const table = rzElement({ tag: 'table', attributes: { class: 'rz-table' } })

    if (args.bordered) {
        table.classList.add('rz-table--bordered')
    }
    if (args.caption) {
        const caption = rzElement({ tag: 'caption', innerText: args.caption })
        table.appendChild(caption)
    }

    if (args.header && args.headerDirection === 'horizontal') {
        const thead = rzElement({ tag: 'thead' })
        const headerRow = rowRenderer(args.body[0], {
            displayHeadName: true,
            type: 'full',
        })
        thead.appendChild(headerRow)
        table.appendChild(thead)
    }

    const tbody = rzElement({ tag: 'tbody' })

    args.body.forEach((rowElement) => {
        const displayHeadName =
            args.header && args.headerDirection === 'vertical'
        const row = rowRenderer(rowElement, { displayHeadName, type: 'first' })
        tbody.appendChild(row)
    })

    table.appendChild(tbody)

    return table
}

export const Default: Story = {
    render: (args) => {
        return rzTableRenderer(args)
    },
}
