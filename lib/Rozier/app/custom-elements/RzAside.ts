const ROUTE_TYPE_PATTERNS = {
    tag: ['/rz-admin/tags'],
    folder: ['/rz-admin/documents', '/rz-admin/folders'],
    node: ['/rz-admin'],
}

export class RzAside extends HTMLElement {
    constructor() {
        super()
        console.log('rz aside initialized')

        this.onPageChange = this.onPageChange.bind(this)
    }

    displayCurrentTree() {
        const activeType = this.getActiveMainTreeType()
        const treeWrappers = this.querySelectorAll<HTMLElement>(
            '[data-tree-wrapper-type]',
        )

        console.log('displayCurrentTree', treeWrappers, activeType)

        treeWrappers.forEach((wrapper) => {
            const elementType = wrapper.getAttribute('data-tree-wrapper-type')
            if (elementType === activeType) {
                wrapper.style.display = 'block'
            } else {
                wrapper.style.display = 'none'
            }
        })
    }

    getActiveMainTreeType() {
        const url = new URL(window.location.href)

        for (const [type, patterns] of Object.entries(ROUTE_TYPE_PATTERNS)) {
            for (const pattern of patterns) {
                const regex = new RegExp(`^${pattern}`)

                if (regex.test(url.pathname)) {
                    return type
                }
            }
        }

        return null
    }

    onPageChange(event: Event) {
        this.displayCurrentTree()
        console.log('page changed', event, this.getActiveMainTreeType())
    }

    connectedCallback() {
        console.log('rz aside connected')
        this.displayCurrentTree()
        window.addEventListener('pagechange', this.onPageChange)
    }

    disconnectedCallback() {
        console.log('rz aside disconnected')
        window.removeEventListener('pagechange', this.onPageChange)
    }
}
