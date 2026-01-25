export const bulkFormCollapsedClassName = 'bulk-actions--collapsed'
export const bulkFormCollapsingClassName = 'bulk-actions--collapsing'

export default function () {
    const bulkSelection = []
    const bulkActionsForms = document.querySelectorAll(
        '.bulk-layout .' + bulkFormCollapsedClassName,
    )
    /**
     * @var {HTMLElement|null}
     */
    const bulkActionsDeselect = document.querySelector(
        '.bulk-layout .uk-button-bulk-deselect',
    )
    /**
     * @var {HTMLInputElement|null}
     */
    const bulkActionsSelectAll = document.querySelector(
        '.bulk-layout input[name="bulk-selection-all"]',
    )
    const bulkActionsSelectAllButton = document.querySelector(
        '.bulk-layout .uk-button-select-all',
    )
    const bulkActionsFormButtons = document.querySelectorAll(
        '.bulk-layout .uk-button-bulk',
    )
    /**
     * @var {NodeListOf<HTMLInputElement>}
     */
    const bulkFormValue = document.querySelectorAll(
        '.bulk-layout input.bulk-form-value',
    )
    /**
     * @var {NodeListOf<HTMLInputElement>}
     */
    const bulkCheckboxes = document.querySelectorAll(
        '.bulk-layout input[name="bulk-selection[]"]',
    )

    bulkActionsForms.forEach((f) =>
        f.addEventListener('transitionend', () => {
            if (f.classList.contains(bulkFormCollapsingClassName)) {
                f.classList.remove(bulkFormCollapsingClassName)
            }
        }),
    )

    const deselectAll = () => {
        bulkCheckboxes.forEach((element) => {
            element.checked = false
            bulkSelection.splice(0, bulkSelection.length)
            bulkFormValue.forEach((e) => (e.value = ''))
        })
        if (bulkActionsSelectAll) {
            bulkActionsSelectAll.checked = false
        }
        closeBulkActionsForms()
    }

    const closeBulkActionsForms = () => {
        window.requestAnimationFrame(() => {
            bulkActionsForms.forEach((f) =>
                f.classList.add(bulkFormCollapsingClassName),
            )
            bulkActionsForms.forEach((f) =>
                f.classList.add(bulkFormCollapsedClassName),
            )
        })
    }
    bulkActionsFormButtons.forEach((button) => {
        if (button.hasAttribute('data-bulk-group')) {
            button.addEventListener('click', () => {
                const bulkGroup = document.getElementById(
                    button.getAttribute('data-bulk-group'),
                )
                if (bulkGroup) {
                    // Close all other bulk groups
                    document.querySelectorAll('.bulk-group').forEach((g) => {
                        if (g !== bulkGroup) {
                            g.classList.remove('bulk-group--open')
                        }
                    })
                    bulkGroup.classList.toggle('bulk-group--open')
                }
            })
        }
    })

    bulkCheckboxes.forEach((element) => {
        element.addEventListener('change', () => {
            if (element.checked) {
                bulkSelection.push(element.value)
            } else {
                bulkSelection.splice(bulkSelection.indexOf(element.value), 1)
            }
            if (bulkSelection.length > 0) {
                bulkActionsForms.forEach((f) =>
                    f.classList.remove(bulkFormCollapsedClassName),
                )
                bulkFormValue.forEach(
                    (e) => (e.value = JSON.stringify(bulkSelection)),
                )
            } else {
                closeBulkActionsForms()
                if (bulkActionsSelectAll) {
                    bulkActionsSelectAll.checked = false
                }
            }
        })
    })

    if (bulkActionsDeselect) {
        bulkActionsDeselect.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            deselectAll()
        })
    }

    if (bulkActionsSelectAllButton) {
        bulkActionsSelectAllButton.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            bulkCheckboxes.forEach((element) => {
                element.checked = true
                bulkSelection.push(element.value)
            })
            bulkActionsForms.forEach((f) =>
                f.classList.remove(bulkFormCollapsedClassName),
            )
            bulkFormValue.forEach(
                (e) => (e.value = JSON.stringify(bulkSelection)),
            )
        })
    }

    if (bulkActionsSelectAll) {
        bulkActionsSelectAll.addEventListener('change', () => {
            if (bulkActionsSelectAll.checked) {
                bulkCheckboxes.forEach((element) => {
                    element.checked = true
                    bulkSelection.push(element.value)
                })
            } else {
                deselectAll()
            }
            if (bulkSelection.length > 0) {
                bulkActionsForms.forEach((f) =>
                    f.classList.remove(bulkFormCollapsedClassName),
                )
                bulkFormValue.forEach(
                    (e) => (e.value = JSON.stringify(bulkSelection)),
                )
            } else {
                bulkActionsForms.forEach((f) =>
                    f.classList.add(bulkFormCollapsedClassName),
                )
            }
        })
    }
}
