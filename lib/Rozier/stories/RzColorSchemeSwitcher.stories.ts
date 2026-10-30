import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzColorSchemeSwitcherOptions,
    rzColorSchemeSwitcherRenderer,
} from '~/utils/storybook/renderer/rzColorSchemeSwitcher'

type Args = RzColorSchemeSwitcherOptions

const meta: Meta<Args> = {
    title: 'Components/Actions/ColorSchemeSwitcher',
    tags: ['autodocs'],
    render: (args) => rzColorSchemeSwitcherRenderer(args),
    args: {
        id: 'color-scheme-switcher-storybook',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}
