import type { Meta, StoryObj } from '@storybook/html-vite';
import '../app/assets/css/RZButton/rz-button.css';

type ButtonArgs = {
	primary: boolean;
	label: string;
	argTypes: {
		emphasis: {
			control: 'radio'
			options: ['high', 'medium', 'low']
		},
		size: {
			control: 'radio'
			options: ['lg', 'md', 'sm']
		},
	},
};

const meta: Meta<ButtonArgs> = {
  /* 👇 The title prop is optional.
   * See https://storybook.js.org/docs/configure/#configure-story-loading
   * to learn how to generate automatic titles
   */
  title: 'Components/RZButton',
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<ButtonArgs>;

/*
 *👇 Render functions are a framework specific feature to allow you control on how the component renders.
 * See https://storybook.js.org/docs/api/csf
 * to learn how to use render functions.
 */
export const Primary: Story = {
  render: (args) => {
    const btn = document.createElement('button');
    btn.className = ['rz-button'].join(' ');

	const span = document.createElement('span');
	span.className = ['rz-button__label'].join(' ');
    span.innerText = args.label;

	const icon = document.createElement('span');
	icon.className = ['rz-button__icon'].join(' ');

	btn.appendChild(span);
	btn.appendChild(icon);

    return btn;
  },
  args: {
    label: 'Button',
  },
};
