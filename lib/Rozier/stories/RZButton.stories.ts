import type { Meta, StoryObj } from '@storybook/html-vite';
import '../app/assets/css/RZButton/rz-button.css';

const EMPHASIS = ['low', 'medium', 'high'] as const;
const SIZES = ['xs', 'sm', 'md', 'lg'] as const;

type ButtonArgs = {
	label: string;
	emphasis: typeof EMPHASIS[number];
	size: typeof SIZES[number];
	disabled: boolean
};

const meta: Meta<ButtonArgs> = {
  	title: 'Components/RZButton',
  	tags: ['autodocs'],
	args: {
		label: 'button label',
		emphasis: 'high',
		size: 'md',
		disabled: false,
	},
	argTypes: {
		emphasis: {
			control: { type: 'select' },
			options: ['high', 'medium', 'low']
		},
		size: {
			control: { type: 'select' },
			options: SIZES
		},
	}
};

export default meta;
type Story = StoryObj<ButtonArgs>;


function createButton(args: ButtonArgs) {
	const buttonNode = document.createElement('button');
	const emphasisClass = args.emphasis && `rz-button--emphasis-${args.emphasis}`;
	const sizeClass = args.size && `rz-button--size-${args.size}`;
	const disabledClass = args.disabled && `rz-button--disabled`;
    buttonNode.className = ['rz-button', emphasisClass, sizeClass, disabledClass].join(' ');

	const labelNode = document.createElement('span');
	labelNode.className = ['rz-button__label'].join(' ');
    labelNode.innerText = args.label;
	buttonNode.appendChild(labelNode);

	const iconNode = document.createElement('span');
	iconNode.className = ['rz-button__icon'].join(' ');
	buttonNode.appendChild(iconNode);

    return buttonNode;
}

export const HighEmphasis: Story = {
  render: (args, ctx) => {
	return createButton(args)
  },
	args: {
		emphasis: 'high',
	},
	parameters: {
		controls: { exclude: ['emphasis'] },
	},
};

export const HighEmphasisList: Story = {
	render: (args, ctx) => {
		const wrapper = document.createElement('div');
		wrapper.style = 'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;';

		SIZES.forEach(size => {
			const btn = createButton({...args, size, label: `High emphasis ${size}`})
			wrapper.appendChild(btn)
		})

		return wrapper;
	},
	args: {
		emphasis: 'high',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'size', 'label'] },
	},
};


export const mediumEmphasis: Story = {
  render: (args, ctx) => {
	return createButton(args)
  },
	args: {
		emphasis: 'medium',
	},
	parameters: {
		controls: { exclude: ['emphasis'] },
	},
};

export const mediumEmphasisList: Story = {
	render: (args, ctx) => {
		const wrapper = document.createElement('div');
		wrapper.style = 'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;';

		SIZES.forEach(size => {
			const btn = createButton({...args, size, label: `Medium emphasis ${size}`})
			wrapper.appendChild(btn)
		})

		return wrapper;
	},
	args: {
		emphasis: 'medium',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'size', 'label'] },
	},
};


export const lowEmphasis: Story = {
  render: (args, ctx) => {
	return createButton(args)
  },
	args: {
		emphasis: 'low',
	},
	parameters: {
		controls: { exclude: ['emphasis'] },
	},
};

export const lowEmphasisList: Story = {
	render: (args, ctx) => {
		const wrapper = document.createElement('div');
		wrapper.style = 'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;';

		SIZES.forEach(size => {
			const btn = createButton({...args, size, label: `Low emphasis ${size}`})
			wrapper.appendChild(btn)
		})

		return wrapper;
	},
	args: {
		emphasis: 'low',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'size', 'label'] },
	},
};
