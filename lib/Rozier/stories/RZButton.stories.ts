import type { Meta, StoryObj } from '@storybook/html-vite';
import '../app/assets/css/rz-button/rz-button.css';

const EMPHASIS = ['low', 'medium', 'high'] as const;
const SIZES = ['xs', 'sm', 'md', 'lg'] as const;

type ButtonArgs = {
	label: string;
	emphasis: typeof EMPHASIS[number];
	size: typeof SIZES[number];
	disabled: boolean
	class: string;
	iconClass: string;
};

const meta: Meta<ButtonArgs> = {
  	title: 'Components/RZButton',
  	tags: ['autodocs'],
	args: {
		label: 'button label',
		disabled: false,
		class: 'rz-button',
		iconClass: 'rz-icon-ri--arrow-drop-right-line',
	},
	argTypes: {
		emphasis: {
			control: { type: 'select' },
			options: [...EMPHASIS, ''],
		},
		size: {
			control: { type: 'select' },
			options: [...SIZES, ''],
		},
	}
};

export default meta;
type Story = StoryObj<ButtonArgs>;


function createButton(args: ButtonArgs) {
	const buttonNode = document.createElement('button');
	const emphasisClass = args.emphasis ? `rz-button--emphasis-${args.emphasis}` : '';
	const sizeClass = args.size ? `rz-button--size-${args.size}` : '';
	const disabledClass = args.disabled ? `rz-button--disabled` : '';
    buttonNode.className = [args.class || 'rz-button', emphasisClass, sizeClass, disabledClass].join(' ').trim();

	const labelNode = document.createElement('span');
	labelNode.className = ['rz-button__label'].join(' ');
    labelNode.innerText = args.label;
	if (args.label) buttonNode.appendChild(labelNode);

	const iconNode = document.createElement('span');
	iconNode.className = ['rz-button__icon', args.iconClass].join(' ');
	if (args.iconClass) buttonNode.appendChild(iconNode);

    return buttonNode;
}


export const Default: Story = {
  	render: (args) => {
		return createButton(args)
  	},
	parameters: {
    	layout: 'centered',
  	},
};

export const HighEmphasis: Story = {
  render: (args) => {
	return createButton(args)
  },
	args: {
		emphasis: 'high',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'class'] },
		layout: 'centered',
	},
};

export const HighEmphasisList: Story = {
	render: (args) => {
		const wrapper = document.createElement('div');
		wrapper.style = 'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;';

		SIZES.forEach(size => {
			const btn = createButton({...args, size, label: `High emphasis ${size}`})
			wrapper.appendChild(btn)
			const btnIconOnly = createButton({...args, size, label: ``})
			wrapper.appendChild(btnIconOnly)
		})

		return wrapper;
	},
	args: {
		emphasis: 'high',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'size', 'label', 'class'] },
	},
};


export const mediumEmphasis: Story = {
  render: (args) => {
	return createButton(args)
  },
	args: {
		emphasis: 'medium',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'class'] },
		layout: 'centered',
	},
};

export const mediumEmphasisList: Story = {
	render: (args) => {
		const wrapper = document.createElement('div');
		wrapper.style = 'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;';

		SIZES.forEach(size => {
			const btn = createButton({...args, size, label: `Medium emphasis ${size}`})
			wrapper.appendChild(btn)

			const btnIconOnly = createButton({...args, size, label: ``})
			wrapper.appendChild(btnIconOnly)
		})

		return wrapper;
	},
	args: {
		emphasis: 'medium',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'size', 'label', 'class'] },
	},
};


export const lowEmphasis: Story = {
  render: (args) => {
	return createButton(args)
  },
	args: {
		emphasis: 'low',
	},
	parameters: {
		controls: { exclude: ['emphasis'] },
		layout: 'centered',
	},
};

export const lowEmphasisList: Story = {
	render: (args) => {
		const wrapper = document.createElement('div');
		wrapper.style = 'display: flex; gap: 16px; flex-wrap: wrap; align-items: center;';

		SIZES.forEach(size => {
			const btn = createButton({...args, size, label: `Low emphasis ${size}`})
			wrapper.appendChild(btn)

			const btnIconOnly = createButton({...args, size, label: ``})
			wrapper.appendChild(btnIconOnly)
		})

		return wrapper;
	},
	args: {
		emphasis: 'low',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'size', 'label', 'class'] },
	},
};


export const liveClassesEditing: Story = {
	render: (args) => {
		return createButton(args);
	},
	args: {
		emphasis: undefined,
		size: undefined,
		class: 'rz-button rz-button--emphasis-high rz-button--size-lg rz-button--disabled',
	},
	parameters: {
		controls: { exclude: ['emphasis', 'size', 'disabled'] },
		layout: 'centered',
	},
};
