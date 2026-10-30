import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import {
    type RzBulkActionsOptions,
    rzBulkActionsRenderer,
} from '~/utils/storybook/renderer/rzBulkActions'

export type RzBulkSectionItemOptions = {
    value: string
    label: string
}

export type RzBulkSectionOptions = {
    items: RzBulkSectionItemOptions[]
    bulkActions: RzBulkActionsOptions
    /** Select-all button of widgets/rz_filters_bar.html.twig (`display_select_all_button`). */
    selectAllButton?: boolean
}

/** Mirrors rz_bulk.select_all_input and rz_bulk.select_input (macros/rz_bulk.html.twig). */
function selectInputRenderer(name: string, label: string, value?: string) {
    const input = document.createElement('input', { is: 'rz-input' })
    input.setAttribute('is', 'rz-input')
    input.type = 'checkbox'
    input.name = name
    input.setAttribute('aria-label', label)
    if (value) input.value = value

    return input
}

function cellRenderer(tag: 'td' | 'th', content: HTMLElement | string) {
    const cell = document.createElement(tag)
    cell.append(content)
    return cell
}

/** Mirrors <rz-bulk-section> in users/list.html.twig: filters bar, bulk actions and rz_table.html.twig. */
export function rzBulkSectionRenderer(args: RzBulkSectionOptions) {
    const element = document.createElement('rz-bulk-section')

    if (args.selectAllButton) {
        const filtersBar = document.createElement('div')
        filtersBar.classList.add('rz-filters-bar')
        filtersBar.appendChild(
            rzButtonRenderer({
                emphasis: 'secondary',
                label: 'Select all',
                iconClass: 'rz-icon-ri--checkbox-line',
                attributes: {
                    type: 'button',
                    'aria-selected': 'false',
                    'data-bulk-select-all-button': '',
                    'selected-class': 'rz-button--selected',
                },
            }),
        )
        element.appendChild(filtersBar)
    }

    element.appendChild(rzBulkActionsRenderer(args.bulkActions))

    const container = document.createElement('div')
    container.classList.add('rz-table-container')
    const table = document.createElement('table', { is: 'rz-table' })
    table.setAttribute('is', 'rz-table')
    table.classList.add('rz-table', 'rz-table--default')
    container.appendChild(table)

    const headRow = document.createElement('tr')
    headRow.append(
        cellRenderer(
            'th',
            selectInputRenderer('bulk-selection-all', 'Select all'),
        ),
        cellRenderer('th', 'Name'),
    )
    table.createTHead().appendChild(headRow)

    const body = table.createTBody()
    args.items.forEach((item) => {
        const row = document.createElement('tr')
        row.append(
            cellRenderer(
                'td',
                selectInputRenderer(
                    'bulk-selection[]',
                    'Select row',
                    item.value,
                ),
            ),
            cellRenderer('td', item.label),
        )
        body.appendChild(row)
    })

    element.appendChild(container)

    return element
}
