import type { Meta, StoryObj } from '@storybook/html-vite'
import { getRootCssVarsByGroups } from './getCssVars'

const meta: Meta = {
    title: 'Integration/Colors',
}

export default meta
type Story = StoryObj

function colorItemRenderer([name, value]: [string, string]) {
    return `
        <div style="flex-basis: 25%;">
            <div style="height: 50px; background: repeating-linear-gradient(-45deg, rgb(204, 204, 204), rgb(204, 204, 204) 1px, rgb(255, 255, 255) 1px, rgb(255, 255, 255) 16px) padding-box white;">
                <div style="height: 100%; width: 100%; background-color: ${value};"></div>
            </div>
            <div style="text-align: center; margin-block: 12px;">
                <span style="user-select: all;">${name}</span>
                <br/>
                <span style="user-select: all;">${value}</span>
            </div>
        </div>`
}

export const Colors: Story = {
    render: () => {
        const colorGroups = getRootCssVarsByGroups()

        const colorGroupNodes = Object.entries(colorGroups)
            .map(([groupName, colors]) => {
                const colorNodes = Object.entries(colors)
                    .map(colorItemRenderer)
                    .join('')

                return `
                    <div style="display: flex; gap: 24px; margin-block: 62px;">
                        <h2 style="min-width: 150px;">${groupName}</h2>
                        <div style="display: flex; align-items: center; flex-wrap: wrap; width: 100%;">${colorNodes}</div>
                    </div>
                `
            })
            .join('')

        return `
            <div style="color: black; max-width: 1000px; margin-inline: auto;">
                ${colorGroupNodes}
            </div>
        `
    },
    args: {},
}
