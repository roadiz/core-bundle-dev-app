import type { Meta, StoryObj } from '@storybook/html-vite';
import riIconNames from '../vite-plugins/iconify/ri-icons'
import rzIconNames from '../vite-plugins/iconify/rz-icons'

type IconArgs = {
  prefix: string | null;
  RZName: string | null;
  RIName: string | null;
  color: string;
  fontSize: string;
};

const meta: Meta<IconArgs> = {
  title: 'Components/Icon',
  tags: ['autodocs'],
  argTypes: {
    prefix: {
      options: ['rz', 'ri'],
      control: { type: 'radio' },
    },
    RZName: {
      options: rzIconNames,
      control: { type: 'select' },
      if: { arg: 'prefix', eq: 'rz' },
    },
    RIName: {
      options: riIconNames,
      control: { type: 'select' },
      if: { arg: 'prefix', eq: 'ri' },
    },
    fontSize: {
      control: 'text',
      description: 'A CSS unit font-size',
    }
  },
};

export default meta;
type Story = StoryObj<IconArgs>;

export const Primary: Story = {
  render: (args) => {
    const span = document.createElement('span');

    span.style.color = args.color
    span.style.fontSize = args.fontSize

    const collectionName = args.prefix === 'rz' ? args.RZName : args.prefix === 'ri' ? args.RIName : ''

    span.className = [
      `${args.prefix}-icon`,
      `${args.prefix}-icon--${collectionName}`,
    ].join(' ');

    return span;
  },

  args: {
    prefix: 'rz',
    RZName: rzIconNames[0],
    RIName: riIconNames[0],
    color: 'black',
    fontSize: '24px',
  },
};


function getCollectionStory(prefix: string, names: string[]) {
  return {
    render: (args) => {
        const container = document.createElement('div');
        container.style = `display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 42px; color: ${args.color}`

        names.forEach(name => {
          const iconWrapper = document.createElement('div');
          iconWrapper.style = `display: flex; flex-direction: column; align-items: center;`

          const label = document.createTextNode(name);

          const icon = document.createElement('span');
          icon.style.color = args.color
          icon.style.fontSize = args.fontSize
          icon.className = [
            `${prefix}-icon`,
            `${prefix}-icon--${name}`,
          ].join(' ');

          iconWrapper.appendChild(icon)
          iconWrapper.appendChild(label)
          container.appendChild(iconWrapper)
        })

        return container
      },
    args: {
      color: '#000',
      fontSize: '24px',
    },
    parameters: {
      controls: { exclude: ['prefix', 'RZName', 'RIName'] },
    },
  } as Story
}

export const RZCollection: Story = getCollectionStory('rz', rzIconNames)
export const RICollection: Story = getCollectionStory('ri', riIconNames)
