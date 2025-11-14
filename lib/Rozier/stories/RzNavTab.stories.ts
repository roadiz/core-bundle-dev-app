import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args } from './RzTablist.stories'
import { rzTabWrapperRenderer } from '../app/utils/storybook/renderer/rzTabWrapper'
import { VARIANTS } from '../app/utils/storybook/renderer/rzTab'

const meta: Meta<Args> = {
    title: 'Components/Tab/Nav',
    tags: ['autodocs'],
    args: {
        variant: 'filled',
        tabs: [
            {
                innerHTML: 'Link Tab 1',
                tag: 'a',
                selected: true,
                attributes: {
                    href: '#',
                    'aria-current': 'page',
                },
            },
            {
                innerHTML: 'Link Tab 2',
                tag: 'a',
                attributes: {
                    href: '#',
                },
            },
        ],
    },
    argTypes: {
        variant: {
            control: 'select',
            options: ['', ...VARIANTS],
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzTabWrapperRenderer(args)
    },
}
