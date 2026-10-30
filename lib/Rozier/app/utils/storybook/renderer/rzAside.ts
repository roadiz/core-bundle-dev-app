import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import {
    type RzTreeOptions,
    rzTreeRenderer,
} from '~/utils/storybook/renderer/rzTree'

export const TREE_TYPES = ['node', 'tag', 'folder'] as const

export type RzAsideTreeWrapperOptions = {
    treeType: (typeof TREE_TYPES)[number]
    /** Without a tree, the wrapper shows the loading button, as rz-aside renders before its first fetch. */
    tree?: RzTreeOptions
}

export type RzAsideOptions = RzAsideTreeWrapperOptions & {
    defaultTranslationId?: string
}

const COMPONENT_CLASS_NAME = 'rz-aside'

const TREE_LABELS = {
    node: { title: 'Node tree', add: 'Add a node' },
    tag: { title: 'Tag tree', add: 'Add a tag' },
    folder: { title: 'Folder tree', add: 'Add a folder' },
}

/** Mirrors components/rz_tree.html.twig for a root tree, without the language switcher. */
export function rzAsideTreeWrapperRenderer(args: RzAsideTreeWrapperOptions) {
    const wrapper = document.createElement('div')
    wrapper.classList.add(
        'rz-tree-wrapper',
        'tree-widget',
        `${args.treeType}-tree-widget`,
    )

    if (!args.tree) {
        wrapper.appendChild(
            rzButtonRenderer({
                tag: 'div',
                label: 'Loading',
                emphasis: 'secondary',
                size: 'md',
                iconClass: 'rz-spinner',
                attributes: {
                    class: `${COMPONENT_CLASS_NAME}__loading-button`,
                    'aria-live': 'polite',
                    role: 'status',
                },
            }),
        )
        return wrapper
    }

    const labels = TREE_LABELS[args.treeType]
    const head = document.createElement('div')
    head.classList.add(`${COMPONENT_CLASS_NAME}__head`)
    head.innerHTML = `<span class="${COMPONENT_CLASS_NAME}__icon rz-icon-ri--node-tree"></span>
<h2 class="${COMPONENT_CLASS_NAME}__title">${labels.title}</h2>`
    head.appendChild(
        rzButtonRenderer({
            tag: 'a',
            iconClass: 'rz-icon-ri--add-line',
            size: 'sm',
            label: labels.add,
            attributes: { class: `${COMPONENT_CLASS_NAME}__button`, href: '#' },
        }),
    )
    wrapper.appendChild(head)

    const tree = rzTreeRenderer(args.tree)
    tree.classList.add(`rz-tree--${args.treeType}`)
    tree.id = `${args.treeType}-root`
    tree.setAttribute('group', `${args.treeType}Tree`)
    tree.setAttribute('data-entity-type', args.treeType)
    wrapper.appendChild(tree)

    return wrapper
}

/** Mirrors patterns/aside.html.twig: the element then fetches its tree. */
export function rzAsideRenderer(args: RzAsideOptions) {
    const element = document.createElement('rz-aside')
    element.classList.add(COMPONENT_CLASS_NAME)
    element.id = COMPONENT_CLASS_NAME
    if (args.defaultTranslationId) {
        element.setAttribute(
            'data-default-translation-id',
            args.defaultTranslationId,
        )
    }

    const body = document.createElement('div')
    body.classList.add(`${COMPONENT_CLASS_NAME}__body`)
    body.appendChild(rzAsideTreeWrapperRenderer({ treeType: args.treeType }))
    element.appendChild(body)

    return element
}
