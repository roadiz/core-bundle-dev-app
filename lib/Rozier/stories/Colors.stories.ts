import type { Meta, StoryObj } from '@storybook/html-vite';

type ColorArg = {};

const meta: Meta<ColorArg> = {
  title: 'Style/Colors',
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<ColorArg>;


// We can’t use getComputedStyle(element) directly to list custom properties,
// getComputedStyle(...).getPropertyValue("--color-primary") only works
// if you already know the variable’s exact name.
function getRootCssVars(prefix: string){
  const computed = getComputedStyle(document.documentElement);
  const vars: Record<string, string> = {};

  for (const sheet of Array.from(document.styleSheets)) {
    let rules: CSSRuleList;

    try {
      rules = sheet.cssRules;
    } catch {
      continue;
    }

    for (const rule of Array.from(rules)) {
      if (!(rule instanceof CSSStyleRule)) continue;
      if (rule.selectorText !== ":root") continue;

      for (const name of Array.from(rule.style)) {
        if (!name.startsWith(prefix)) continue;
        vars[name] = computed.getPropertyValue(name).trim();
      }
    }
  }

  return vars;
}


function sortByLastNumber(cssVarNameA: string, cssVarNameB: string) {
  const aParts = cssVarNameA.split('-')
  const bParts = cssVarNameB.split('-')
  const aNum = parseInt(aParts[aParts.length - 1]) || 0
  const bNum = parseInt(bParts[bParts.length - 1]) || 0
  return aNum - bNum
}

function getRootCssVarsByGroups() {
    const colors = getRootCssVars("--color-")

    return Object.entries(colors)
      .sort((entryA, entryB) => {
          return sortByLastNumber(entryA[0], entryB[0])
        })
      .reduce((groups, [name, value]) => {
        const groupName = name.split('-')[3] || 'ungrouped'
        if (groupName in groups) {
          groups[groupName][name] = value
        } else {
          Object.assign(groups, { [groupName]: { ...groups[groupName], [name]: value } })
        }
        return groups
      }, {} as Record<string, Record<string, string>>)
}

export const Primary: Story = {
  render: (args) => {

    const colorGroups = getRootCssVarsByGroups()

    const colorGroupNodes = Object.entries(colorGroups).map(([groupName, colors]) => {

      const colorNodes = Object.entries(colors)
        .map(([colorName, colorValue]) => {
          return `
            <div style="flex-basis: 25%;">
              <div style="height: 80px; background-color: ${colorValue};"></div>
              <div style="text-align: center; margin-block: 12px;">
                ${colorName}
                <br/>
                <span style="user-select: all;">${colorValue}</span>
                </div>
            </div>
          `}).join('')

      return `
        <div style="display: flex; gap: 24px; margin-bottom: 62px;">
          <div style="min-width: 150px;">${groupName}</div>
          <div style="display: flex; align-items: center; flex-wrap: wrap; width: 100%;">${colorNodes}</div>
        </div>
      `;
    }).join('');

    return `
      <div style="color: black;">
        ${colorGroupNodes}
      </div>
    `;

  },
  args: {},
};
