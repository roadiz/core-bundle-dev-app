import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import {
    type RzDropdownOptions,
    rzDropdownRenderer,
} from '~/utils/storybook/renderer/rzDropDown'

export const POPOVER_PLACEMENTS = [
    'bottom-end',
    'bottom-start',
    'top-end',
    'top-start',
] as const

export type RzNodeTreeContextualMenuOptions = {
    id?: string
    nodeId: string
    contextualMenuPath: string
    statusPath?: string
    duplicatePath?: string
    pastePath?: string
    editPositionPath?: string
    copiedTrans?: string
    popoverPlacement?: (typeof POPOVER_PLACEMENTS)[number]
    /** Story-only prefilled popover. singleNode.html.twig renders none: the element fetches `contextualMenuPath`. */
    content?: RzDropdownOptions
}

/** Mirrors widgets/nodeTree/contextualMenu.html.twig (without the node badges), fetched on first opening. */
export const DEFAULT_CONTEXTUAL_MENU_CONTENT: RzDropdownOptions = {
    title: 'Actions | Home',
    items: [
        [
            {
                tag: 'a',
                iconClass: 'rz-icon-ri--add-line',
                label: 'Add a child node',
                attributes: { href: '#' },
            },
            {
                tag: 'a',
                iconClass: 'rz-icon-ri--edit-line',
                label: 'Edit node',
                attributes: { href: '#' },
            },
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--arrow-up-line',
                label: 'Move to first position',
                attributes: { type: 'button', command: '--move-first' },
            },
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--arrow-down-line',
                label: 'Move to last position',
                attributes: { type: 'button', command: '--move-last' },
            },
        ],
        [
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--file-copy-line',
                label: 'Copy',
                attributes: { type: 'button', command: '--copy' },
            },
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--file-copy-line',
                label: 'Paste inside',
                attributes: {
                    type: 'button',
                    command: '--paste-inside',
                    disabled: 'true',
                },
            },
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--file-copy-line',
                label: 'Paste after',
                attributes: {
                    type: 'button',
                    command: '--paste-after',
                    disabled: 'true',
                },
            },
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--file-copy-line',
                label: 'Duplicate',
                attributes: { type: 'button', command: '--duplicate' },
            },
        ],
        [
            {
                tag: 'button',
                iconClass: 'rz-icon-ri--eye-off-line',
                label: 'Hide',
                attributes: {
                    type: 'button',
                    command: '--update-status',
                    'data-status': 'visible',
                    'data-value': '0',
                },
            },
        ],
    ],
}

let instanceCounter = 0

/** Mirrors the contextual_menu block of widgets/nodeTree/singleNode.html.twig. */
export function rzNodeTreeContextualMenuRenderer(
    args: RzNodeTreeContextualMenuOptions,
) {
    const id = args.id || `node-contextual-menu-${++instanceCounter}`
    const popoverId = `${id}-popover`

    const element = document.createElement('rz-node-tree-contextual-menu')
    element.classList.add('rz-node-contextual-menu')
    element.id = id
    element.setAttribute('data-node-id', args.nodeId)
    element.setAttribute('data-contextual-menu-path', args.contextualMenuPath)

    const optionalAttributes: Record<string, string | undefined> = {
        'data-node-status-path': args.statusPath,
        'data-node-duplicate-path': args.duplicatePath,
        'data-node-paste-path': args.pastePath,
        'data-node-copied-trans': args.copiedTrans,
        'data-node-edit-position-path': args.editPositionPath,
        'popover-placement': args.popoverPlacement || 'bottom-end',
    }
    Object.entries(optionalAttributes).forEach(([key, value]) => {
        if (value) element.setAttribute(key, value)
    })

    element.appendChild(
        rzButtonRenderer({
            iconClass: 'rz-icon-ri--more-line',
            emphasis: 'tertiary',
            size: 'sm',
            attributes: {
                class: 'tree-contextualmenu-button',
                'aria-label': 'Show actions',
                popovertarget: popoverId,
                type: 'button',
            },
        }),
    )

    // Without a popover, the element creates an idle placeholder and fetches its content
    if (args.content) {
        const popover = document.createElement('div')
        popover.id = popoverId
        popover.setAttribute('popover', '')
        popover.setAttribute('data-popover-content-state', 'fetched')
        popover.setAttribute('data-contextual-menu-popover', '')
        popover.appendChild(rzDropdownRenderer(args.content))
        // contextualMenu.html.twig cannot know the instance ID
        popover.querySelectorAll('button[command]').forEach((button) => {
            button.setAttribute('commandfor', id)
        })
        element.appendChild(popover)
    }

    return element
}
