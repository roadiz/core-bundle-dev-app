import type { Meta, StoryObj } from '@storybook/html-vite'
import { http, HttpResponse } from 'msw'
import { rzDropdownRenderer } from '~/utils/storybook/renderer/rzDropDown'
import {
    DEFAULT_CONTEXTUAL_MENU_CONTENT,
    POPOVER_PLACEMENTS,
    type RzNodeTreeContextualMenuOptions,
    rzNodeTreeContextualMenuRenderer,
} from '~/utils/storybook/renderer/rzNodeTreeContextualMenu'

type Args = RzNodeTreeContextualMenuOptions

const CONTEXTUAL_MENU_PATH = '/rz-admin/nodes/1/contextual-menu'
const UPDATE_PATHS = {
    statusPath: '/rz-admin/ajax/nodes/statuses',
    duplicatePath: '/rz-admin/ajax/nodes/duplicate',
    pastePath: '/rz-admin/ajax/nodes/paste',
    editPositionPath: '/rz-admin/ajax/nodes/position',
}

const meta: Meta<Args> = {
    title: 'Components/Overlay/NodeTreeContextualMenu',
    tags: ['autodocs'],
    render: (args) => rzNodeTreeContextualMenuRenderer(args),
    args: {
        nodeId: '1',
        contextualMenuPath: CONTEXTUAL_MENU_PATH,
        copiedTrans: 'Node copied to clipboard',
        popoverPlacement: 'bottom-end',
        ...UPDATE_PATHS,
    },
    argTypes: {
        popoverPlacement: {
            control: 'select',
            options: POPOVER_PLACEMENTS,
        },
    },
    parameters: {
        layout: 'centered',
        msw: {
            handlers: [
                http.get(CONTEXTUAL_MENU_PATH, () =>
                    HttpResponse.html(
                        rzDropdownRenderer(DEFAULT_CONTEXTUAL_MENU_CONTENT)
                            .outerHTML,
                    ),
                ),
                ...Object.values(UPDATE_PATHS).map((path) =>
                    http.post(path, () => HttpResponse.json({})),
                ),
            ],
        },
    },
}

export default meta
type Story = StoryObj<Args>

/** As singleNode.html.twig renders it: the element fetches contextualMenu.html.twig (mocked) on first opening. */
export const Default: Story = {}

/** Story-only: the popover content is prefilled, so its items follow the `content` control. */
export const Prefilled: Story = {
    args: {
        content: DEFAULT_CONTEXTUAL_MENU_CONTENT,
    },
}
