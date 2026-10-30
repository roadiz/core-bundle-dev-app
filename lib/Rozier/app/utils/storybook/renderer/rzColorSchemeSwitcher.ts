import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzDropdownRenderer } from '~/utils/storybook/renderer/rzDropDown'
import { rzPopoverRenderer } from '~/utils/storybook/renderer/rzPopover'

const COMMAND = '--update-color-scheme'

export const COLOR_SCHEMES = [
    {
        value: 'system',
        label: 'System',
        iconClass: 'rz-icon-ri--computer-line',
    },
    { value: 'light', label: 'Light', iconClass: 'rz-icon-ri--sun-line' },
    { value: 'dark', label: 'Dark', iconClass: 'rz-icon-ri--moon-line' },
] as const

export type RzColorSchemeSwitcherOptions = {
    id?: string
    attributes?: Record<string, string>
}

// Mirrors components/rz_color_scheme_switcher.html.twig
export function rzColorSchemeSwitcherRenderer(
    args: RzColorSchemeSwitcherOptions = {},
) {
    const id = args.id || 'color-theme-switcher'

    const switcher = document.createElement('rz-color-scheme-switcher')
    rzDropdownRenderer(
        {
            items: [
                COLOR_SCHEMES.map((scheme) => ({
                    tag: 'button',
                    label: scheme.label,
                    rightIconClass: scheme.iconClass,
                    selected: scheme.value === 'system',
                    attributes: {
                        type: 'button',
                        'data-value': scheme.value,
                        command: COMMAND,
                        commandfor: id,
                    },
                })),
            ],
        },
        switcher,
    )

    const { popover } = rzPopoverRenderer({
        placement: 'bottom-end',
        offset: 12,
        targetElement: {
            element: rzButtonRenderer({
                size: 'sm',
                iconClass: 'rz-icon-ri--color-filter-line',
                attributes: {
                    type: 'button',
                    'aria-label': 'Show color theme',
                },
            }),
        },
        popoverElement: { id, element: switcher },
    })

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        popover.setAttribute(key, value)
    })

    return popover
}
