import type { Meta, StoryObj } from '@storybook/html-vite'
import riIconNames from '../../vite-plugins/iconify/collections/ri'
import rzIconNames from '../../vite-plugins/iconify/collections/rz'
import { iconItemRenderer, iconRenderer, type IconArgs } from './iconItem'

const meta: Meta<IconArgs> = {
  title: 'Integration/Icon',
  argTypes: {
    prefix: {
      options: ['rz', 'ri'],
      control: { type: 'radio' },
    },
    RzName: {
      options: rzIconNames,
      control: { type: 'select' },
      if: { arg: 'prefix', eq: 'rz' },
    },
    RiName: {
      options: riIconNames,
      control: { type: 'select' },
      if: { arg: 'prefix', eq: 'ri' },
    },
    fontSize: {
      control: 'text',
      description: 'A CSS unit font-size',
    },
  },
  tags: ['autodocs'],
}

export default meta
type Story = StoryObj<IconArgs>

export const Primary: Story = {
  render: (args) => {
    return iconRenderer(args)
  },
  parameters: {
    layout: 'centered',
  },
  args: {
    prefix: 'rz',
    RzName: rzIconNames[0],
    RiName: riIconNames[0],
    color: '',
    fontSize: '',
  },
}

function getCollectionStory(prefix: string, names: string[]) {
  return {
    render: (args) => {
      const container = document.createElement('div')
      container.style = `
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 42px;
        margin-inline: auto;
        max-width: 1000px;
        `

      names.forEach((name) => {
        const item = iconItemRenderer({ ...args, prefix, RzName: name, RiName: name })
        container.appendChild(item)
      })

      return container
    },
    args: {
      color: '',
      fontSize: '',
    },
    parameters: {
      controls: { exclude: ['prefix', 'RzName', 'RiName'] },
    },
  } as Story
}

export const RzCollection: Story = getCollectionStory('rz', rzIconNames)
export const RiCollection: Story = getCollectionStory('ri', riIconNames)
