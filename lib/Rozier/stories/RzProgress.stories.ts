import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzProgressOptions,
    rzProgressRenderer,
    STATES,
} from '~/utils/component-renderer/rzProgress'

type Args = RzProgressOptions

const meta: Meta<Args> = {
    title: 'Components/Progress',
    tags: ['autodocs'],
    args: {
        label: 'Translation quota',
        value: 1712400,
        max: 5000000,
        hint: '1 712 400 of 5 000 000 characters',
    },
    argTypes: {
        state: {
            options: ['', ...STATES],
            control: { type: 'radio' },
            type: 'string',
            description:
                'If no state class is provided, the success color is applied by default.',
        },
        projection: {
            description:
                'Amount an upcoming operation is expected to consume, in the same unit as value. Renders a hatched segment after the consumed one.',
        },
        stat: {
            description:
                'Add rz-progress--stat: the value becomes the headline, above its label.',
        },
    },
    parameters: {
        layout: 'padded',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => rzProgressRenderer(args),
}

export const Warning: Story = {
    render: (args) => rzProgressRenderer(args),
    args: {
        value: 4351900,
        state: 'warning',
        hint: '4 351 900 of 5 000 000 characters',
    },
}

export const LimitReached: Story = {
    render: (args) => rzProgressRenderer(args),
    args: {
        value: 5000000,
        state: 'error',
        hint: 'Quota reached — machine translation unavailable',
    },
}

/**
 * `projection` shows what an upcoming operation will consume, on top of what is
 * already used. The hatched segment is decorative: the `<progress>` element only
 * announces the consumed value, and the projection is carried by the hint text.
 */
export const WithProjection: Story = {
    render: (args) => rzProgressRenderer(args),
    args: {
        projection: 902300,
        hint: 'This translation will consume 902 300 characters for 46 sources. 2 385 300 will remain.',
    },
}

export const ProjectionOverQuota: Story = {
    render: (args) => rzProgressRenderer(args),
    args: {
        value: 4351900,
        projection: 902300,
        state: 'error',
        hint: 'This translation will consume 902 300 characters for 46 sources: 254 200 more than the remaining quota.',
    },
}

export const Stat: Story = {
    render: (args) => rzProgressRenderer(args),
    args: {
        value: 4351900,
        state: 'warning',
        stat: true,
        hint: '4 351 900 of 5 000 000 characters · DeepL',
    },
}
