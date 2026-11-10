import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzNodeIconRenderer } from '~/utils/storybook/renderer/rzNodeIcon'
import {
    rzNodeTreeContextualMenuRenderer,
    DEFAULT_CONTEXTUAL_MENU_CONTENT,
} from '~/utils/storybook/renderer/rzNodeTreeContextualMenu'
import {
    COMPONENT_CLASS_NAME,
    type RzTreeOptions,
    rzTreeRenderer,
} from '~/utils/storybook/renderer/rzTree'

type Args = RzTreeOptions

const meta: Meta<Args> = {
    title: 'Components/Navigation/Tree',
    tags: ['autodocs'],
    render: (args) => rzTreeRenderer(args),
    args: {
        iconColor: '',
        childNodes: false,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    args: {
        items: [
            {
                label: 'Menu 1',
                iconClass: 'rz-icon-ri--home-2-fill',
                expanded: true,
                children: [
                    {
                        label: 'item 1.1',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                    {
                        label: 'item 1.2',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 1.3',
                        expanded: true,
                        iconClass: 'rz-icon-rz--status-published-fill',
                        children: [
                            {
                                label: 'item 1.3.1',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                            },
                            {
                                label: 'item 1.3.2',
                                iconClass: 'rz-icon-rz--status-published-fill',
                                children: [
                                    {
                                        label: 'item 1.3.2.1',
                                        iconClass:
                                            'rz-icon-rz--status-draft-fill',
                                    },
                                ],
                            },
                        ],
                    },
                    {
                        label: 'item 1.4',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                ],
            },
            {
                label: 'Page 1',
                iconClass: 'rz-icon-rz--status-published-fill',
            },
            {
                label: 'Page 2',
                iconClass: 'rz-icon-rz--status-published-fill',
            },
            {
                label: 'Menu 2',
                iconClass: 'rz-icon-rz--status-draft-fill',
                children: [
                    {
                        label: 'item 2.1',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                    {
                        label: 'item 2.2',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                ],
            },
            {
                label: 'Menu 3',
                iconClass: 'rz-icon-rz--status-draft-fill',
                children: [
                    {
                        label: 'item 3.1',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        children: [
                            {
                                label: 'item 3.1.1',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                            },
                        ],
                    },
                ],
            },
            {
                label: 'Menu 4',
                iconClass: 'rz-icon-rz--status-draft-fill',
                children: [
                    {
                        label: 'item 1.1',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 1.2',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                    {
                        label: 'item 1.3',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        children: [
                            {
                                label: 'item 1.3.1',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                            },
                            {
                                label: 'item 1.3.2',
                                iconClass: 'rz-icon-rz--status-published-fill',
                                children: [
                                    {
                                        label: 'item 1.3.2.1',
                                        iconClass:
                                            'rz-icon-rz--status-draft-fill',
                                    },
                                ],
                            },
                        ],
                    },
                ],
            },
        ],
    },
}

export const ChildNodes: Story = {
    args: {
        childNodes: true,
        items: [
            {
                label: 'Menu 1',
                iconClass: 'rz-icon-rz--status-published-fill',
                expanded: true,
                children: [
                    {
                        label: 'item 1.1',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 1.3',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        expanded: true,
                        children: [
                            {
                                label: 'item 1.3.1',
                                iconClass: 'rz-icon-rz--status-published-fill',
                            },
                            {
                                label: 'item 1.3.2',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                                children: [
                                    {
                                        label: 'item 1.3.2.1',
                                        iconClass:
                                            'rz-icon-rz--status-published-fill',
                                    },
                                ],
                            },
                        ],
                    },
                    {
                        label: 'item 1.4',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                ],
            },
            { label: 'Page 2', iconClass: 'rz-icon-rz--status-published-fill' },
            {
                label: 'Menu 2',
                iconClass: 'rz-icon-rz--status-draft-fill',
                expanded: true,
                children: [
                    {
                        label: 'item 2.1',
                        iconClass: 'rz-icon-rz--status-published-fill',
                    },
                    {
                        label: 'item 2.2',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                    },
                ],
            },
        ],
    },
}

function createContextualMenuAction(): HTMLElement {
    // Prefilled content: the contextual menu path does not exist in Storybook
    return rzNodeTreeContextualMenuRenderer({
        nodeId: '1',
        contextualMenuPath: '#',
        copiedTrans: 'Node copied to clipboard',
        content: DEFAULT_CONTEXTUAL_MENU_CONTENT,
    })
}

export const WithContextualMenu: Story = {
    args: {
        items: [
            {
                label: 'Home',
                iconClass: 'rz-icon-ri--home-2-fill',
                expanded: true,
                actions: [createContextualMenuAction()],
                children: [
                    {
                        label: 'About us',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Contact',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Services',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        expanded: true,
                        actions: [createContextualMenuAction()],
                        children: [
                            {
                                label: 'Web development',
                                iconClass: 'rz-icon-rz--status-draft-fill',
                                actions: [createContextualMenuAction()],
                            },
                            {
                                label: 'Mobile apps',
                                iconClass: 'rz-icon-rz--status-published-fill',
                                actions: [createContextualMenuAction()],
                            },
                        ],
                    },
                ],
            },
            {
                label: 'Blog',
                iconClass: 'rz-icon-rz--status-draft-fill',
                actions: [createContextualMenuAction()],
            },
            {
                label: 'Products',
                iconClass: 'rz-icon-rz--status-published-fill',
                actions: [createContextualMenuAction()],
                children: [
                    {
                        label: 'Product A',
                        iconClass: 'rz-icon-rz--status-draft-fill',
                        actions: [createContextualMenuAction()],
                    },
                    {
                        label: 'Product B',
                        iconClass: 'rz-icon-rz--status-published-fill',
                        actions: [createContextualMenuAction()],
                    },
                ],
            },
        ],
    },
}

export const WithThumbnail: Story = {
    args: {
        childNodes: true,
        items: [
            {
                label: 'Gallery',
                icon: rzNodeIconRenderer({
                    nodeId: '2',
                    status: 'published',
                    size: 'medium',
                    color: 'blue',
                }),
                expanded: true,
                children: [
                    {
                        label: 'Photo 1',
                        icon: rzNodeIconRenderer({
                            nodeId: '2',
                            size: 'medium',
                            status: 'draft',
                            color: 'salmon',
                        }),
                    },
                    {
                        label: 'Photo 2',
                        icon: rzNodeIconRenderer({
                            nodeId: '3',
                            size: 'medium',
                            status: 'published',
                        }),
                    },
                    {
                        label: 'Album',
                        icon: rzNodeIconRenderer({
                            nodeId: '4',
                            size: 'medium',
                            status: 'draft',
                        }),
                        expanded: true,
                        children: [
                            {
                                label: 'Photo 3',
                                icon: rzNodeIconRenderer({
                                    nodeId: '5',
                                    size: 'medium',
                                    status: 'published',
                                }),
                            },
                            {
                                label: 'Photo 4',
                                icon: rzNodeIconRenderer({
                                    nodeId: '6',
                                    size: 'medium',
                                    status: 'published',
                                }),
                            },
                        ],
                    },
                ],
            },
        ],
    },
}

const nextFrame = () => new Promise(requestAnimationFrame)

/**
 * Stack tree rows with tags. Drag the resize handle: tags fold into the popover one by one when
 * the row narrows, the title keeps its minimum width, the button counts the folded ones.
 */
export const WithTags: Story = {
    args: {
        items: [
            {
                label: 'A page with many tags and a rather long title',
                iconClass: 'rz-icon-rz--status-published-fill',
                tags: [
                    'Culture',
                    'Théâtre',
                    'Saison 2026',
                    'Jeune public',
                    'Création',
                    'Festival',
                    'Partenariat',
                    'Archive',
                ],
            },
            {
                label: 'Two tags only',
                iconClass: 'rz-icon-rz--status-draft-fill',
                tags: ['Culture', 'Théâtre'],
            },
            {
                label: 'No tag',
                iconClass: 'rz-icon-rz--status-published-fill',
            },
        ],
    },
    decorators: [
        (story) => {
            const container = document.createElement('div')
            container.style.cssText =
                'resize: horizontal; overflow: hidden; width: 900px; max-width: 100%; padding: 8px; border: 1px dashed #ccc;'
            container.appendChild(story() as HTMLElement)

            return container
        },
    ],
    play: async ({ canvasElement, args }) => {
        await nextFrame()
        await nextFrame()

        const rows = Array.from(
            canvasElement.querySelectorAll<HTMLElement>(
                `.${COMPONENT_CLASS_NAME}__item__node__inner`,
            ),
        )
        rows.forEach((row, i) => {
            const total = args.items[i].tags?.length ?? 0
            const tags = row.querySelector<HTMLElement>(
                `.${COMPONENT_CLASS_NAME}__item__tags`,
            )
            if (!tags) {
                if (total) throw new Error(`row ${i}: tags wrapper missing`)
                return
            }
            const inline = tags.querySelectorAll(':scope > rz-badge').length
            const counter = tags.querySelector('.rz-button__label')
            const folded = total - inline
            if (folded > 0 && counter?.textContent !== String(folded)) {
                throw new Error(
                    `row ${i}: counter "${counter?.textContent}" for ${folded} folded tags`,
                )
            }
            if (row.scrollWidth > row.clientWidth + 1) {
                throw new Error(`row ${i}: overflows`)
            }
            const label = row.querySelector<HTMLElement>(
                `.${COMPONENT_CLASS_NAME}__item__label`,
            )
            if (label && inline > 0 && label.offsetWidth < 120) {
                throw new Error(
                    `row ${i}: title squeezed to ${label.offsetWidth}px`,
                )
            }
        })
    },
}
