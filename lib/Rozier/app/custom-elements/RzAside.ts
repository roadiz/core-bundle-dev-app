import { RzTreeWrapper } from './RzTreeWrapper'
import { fadeOut, fadeIn } from '~/utils/animation'

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

    async displayCurrentTree() {
        const activeType = this.getActiveMainTreeType()
        const treeWrappers =
            this.querySelectorAll<RzTreeWrapper>('rz-tree-wrapper')

        // Ensure all custom elements are defined before manipulating them
        await customElements.whenDefined('rz-tree-wrapper')

        treeWrappers.forEach(async (treeWrapper) => {
            if (treeWrapper.getAttribute('type') === activeType) {
                console.log('Show tree', treeWrapper)
                await treeWrapper.refreshTree?.()

                const loaderEl = this.querySelectorAll('[data-loader-element]')
                loaderEl.forEach(async (el) => {
                    await fadeOut(el)
                    el.remove()
                })
                treeWrapper.removeAttribute('hidden')
                await fadeIn(treeWrapper)
            } else {
                await fadeOut(treeWrapper)
                treeWrapper.setAttribute('hidden', 'true')
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

    async onPageChange(event: Event) {
        await this.displayCurrentTree()
        console.log('page changed', event, this.getActiveMainTreeType())
    }

    async connectedCallback() {
        console.log('rz aside connected')
        await this.displayCurrentTree()
        window.addEventListener('pagechange', this.onPageChange)
    }

    disconnectedCallback() {
        console.log('rz aside disconnected')
        window.removeEventListener('pagechange', this.onPageChange)
    }
}
