// We can’t use getComputedStyle(element) directly to list custom properties,
// getComputedStyle(...).getPropertyValue("--color-primary") only works
// if you already know the variable’s exact name.
function getRootCssVars(prefix: string) {
    const computed = getComputedStyle(document.documentElement)
    const vars: Record<string, string> = {}

    for (const sheet of Array.from(document.styleSheets)) {
        let rules: CSSRuleList

        try {
            rules = sheet.cssRules
        } catch {
            continue
        }

        for (const rule of Array.from(rules)) {
            if (!(rule instanceof CSSStyleRule)) continue
            if (rule.selectorText !== ':root') continue

            for (const name of Array.from(rule.style)) {
                if (!name.startsWith(prefix)) continue
                vars[name] = computed.getPropertyValue(name).trim()
            }
        }
    }

    return vars
}

function sortByLastNumber(cssVarNameA: string, cssVarNameB: string) {
    const aParts = cssVarNameA.split('-')
    const bParts = cssVarNameB.split('-')
    const aNum = parseInt(aParts[aParts.length - 1]) || 0
    const bNum = parseInt(bParts[bParts.length - 1]) || 0
    return aNum - bNum
}

function getRootCssVarsByGroups() {
    const colors = getRootCssVars('--color-')

    return Object.entries(colors)
        .sort((entryA, entryB) => {
            return sortByLastNumber(entryA[0], entryB[0])
        })
        .reduce(
            (groups, [name, value]) => {
                // CSS variable naming convention: --color-group-variant-number
                // partsType: ['prefix', 'groupName', 'identifier', 'alphaNumber']
                const parts = name.replace('--', '').split('-')
                const groupName = parts?.[1] || 'ungrouped'

                if (groupName in groups) {
                    groups[groupName][name] = value
                } else {
                    Object.assign(groups, {
                        [groupName]: { [name]: value },
                    })
                }
                return groups
            },
            {} as Record<string, Record<string, string>>,
        )
}

export { getRootCssVarsByGroups }
