import Sortable from 'sortablejs/modular/sortable.core.esm.js'

export default class RzTreeItem extends HTMLElement {
    sortables: Sortable[]
    rootNode: HTMLElement | null = null
    group: string

    constructor() {
        super()

        this.sortables = []
    }

    connectedCallback() {
        this.rootNode = this.querySelector('[role="tree"]')

        this.group = this.getAttribute('group') || 'tree'

        this.initSortable()
    }

    disconnectedCallback() {
        this.destroySortable()
    }

    // SORTABLE
    initSortable() {
        const sortableLists = this?.querySelectorAll('.rz-tree__list')

        if (!sortableLists) return

        for (let i = 0; i < sortableLists.length; i++) {
            this.sortables.push(
                new Sortable(sortableLists[i], {
                    group: this.group,
                    animation: 150,
                    filter: '.rz-tree__item--locked',
                    handle: '.rz-tree__item__handle',
                    chosenClass: 'rz-tree__item--chosen',
                    dragClass: 'rz-tree__item--drag',
                    ghostClass: 'rz-tree__item--ghost',
                }),
            )
        }
    }

    destroySortable() {
        this.sortables.forEach((sortable) => sortable.destroy())
        this.sortables = []
    }
}
