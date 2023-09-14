export default function () {
    const bulkSelection = []
    const bulkFormCollapsedClassName = 'bulk-actions--collapsed'
    const bulkFormCollapsingClassName = 'bulk-actions--collapsing'
    const bulkActionsForms = document.querySelectorAll('.bulk-layout .' + bulkFormCollapsedClassName)
    /**
     * @var {HTMLElement|null}
     */
    const bulkActionsDeselect = document.querySelector('.bulk-layout .uk-button-bulk-deselect')
    /**
     * @var {HTMLInputElement|null}
     */
    const bulkActionsSelectAll = document.querySelector(".bulk-layout input[name='bulk-selection-all']")
    /**
     * @var {NodeListOf<HTMLInputElement>}
     */
    const bulkFormValue = document.querySelectorAll('.bulk-layout input.bulk-form-value')
    /**
     * @var {NodeListOf<HTMLInputElement>}
     */
    const bulkCheckboxes = document.querySelectorAll(".bulk-layout input[name='bulk-selection[]']")

    bulkActionsForms.forEach((f) =>
        f.addEventListener('transitionend', (e) => {
            if (f.classList.contains(bulkFormCollapsingClassName)) {
                f.classList.remove(bulkFormCollapsingClassName)
            }
        })
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
            bulkActionsForms.forEach((f) => f.classList.add(bulkFormCollapsingClassName))
            bulkActionsForms.forEach((f) => f.classList.add(bulkFormCollapsedClassName))
        })
    }
    const openBulkActionsForms = () => {
        window.requestAnimationFrame(() => {
            bulkActionsForms.forEach((f) => f.classList.add(bulkFormCollapsingClassName))
            bulkActionsForms.forEach((f) => f.classList.remove(bulkFormCollapsedClassName))
        })
    }

    bulkCheckboxes.forEach((element) => {
        element.addEventListener('change', (e) => {
            if (element.checked) {
                bulkSelection.push(element.value)
            } else {
                bulkSelection.splice(bulkSelection.indexOf(element.value), 1)
            }
            if (bulkSelection.length > 0) {
                bulkActionsForms.forEach((f) => f.classList.remove(bulkFormCollapsedClassName))
                bulkFormValue.forEach((e) => (e.value = JSON.stringify(bulkSelection)))
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

    if (bulkActionsSelectAll) {
        bulkActionsSelectAll.addEventListener('change', (e) => {
            if (bulkActionsSelectAll.checked) {
                bulkCheckboxes.forEach((element) => {
                    element.checked = true
                    bulkSelection.push(element.value)
                })
            } else {
                deselectAll()
            }
            if (bulkSelection.length > 0) {
                bulkActionsForms.forEach((f) => f.classList.remove(bulkFormCollapsedClassName))
                bulkFormValue.forEach((e) => (e.value = JSON.stringify(bulkSelection)))
            } else {
                bulkActionsForms.forEach((f) => f.classList.add(bulkFormCollapsedClassName))
            }
        })
    }
}
