import type { Meta, StoryObj } from '@storybook/html-vite';

type ButtonArgs = {};

const meta: Meta<ButtonArgs> = {
  title: 'Style/Colors',
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<ButtonArgs>;


function getRootCssVars(prefix = "--color-") {
  const vars: Record<string, string> = {};

  for (const sheet of document.styleSheets) {
    try {
      for (const rule of sheet.cssRules) {
        if (!('selectorText' in rule) || rule?.selectorText !== ":root") return

          const style = rule?.style || []
          for (let i = 0; i < style.length; i++) {
            const name = style[i];
            if (!name.startsWith(prefix)) return

            vars[name] = style.getPropertyValue(name).trim();
          }
      }
    } catch (e) {
      continue;
    }
  }

  return vars;
}

export const Primary: Story = {
  render: (args) => {
    console.log(getRootCssVars());

    const div = document.createElement('div');
    return div
  },
  args: {},
};
