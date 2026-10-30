import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzFormFieldHeadRenderer } from '~/utils/storybook/renderer/rzFormField'

export type RzGeotagOptions = {
    id: string
    label: string
    multiple?: boolean
    /** GeoJSON: a Feature, or a FeatureCollection when `multiple`. */
    value?: string
    help?: string
}

const COMPONENT_CLASS_NAME = 'rz-geotag'

/** Mirrors the item_detail macro of widgets/rz_geotag_field.html.twig. */
function itemDetailRenderer(
    componentId: string,
    itemId = '',
    name = '',
    content = '',
) {
    const item = document.createElement('div')
    item.classList.add(`${COMPONENT_CLASS_NAME}__item`)
    item.id = itemId
    item.innerHTML = `<span class="${COMPONENT_CLASS_NAME}__item__icon rz-icon-ri--map-pin-line"></span>
<div class="${COMPONENT_CLASS_NAME}__item__content">
    <span class="${COMPONENT_CLASS_NAME}__item__name" data-item-detail-name title="${name}">${name}</span>
    <span class="${COMPONENT_CLASS_NAME}__item__subtitle" data-item-detail-content>${content}</span>
</div>`

    const buttons = [
        { command: '--target-marker', iconClass: 'rz-icon-ri--crosshair-line' },
        {
            command: '--delete-marker',
            iconClass: 'rz-icon-ri--delete-bin-7-line',
            color: 'danger' as const,
        },
    ]
    buttons.forEach(({ command, iconClass, color }) => {
        item.appendChild(
            rzButtonRenderer({
                size: 'xs',
                emphasis: 'tertiary',
                color,
                iconClass,
                attributes: {
                    class: `${COMPONENT_CLASS_NAME}__item__button`,
                    type: 'button',
                    commandfor: componentId,
                    command,
                    'data-item-detail-id': itemId,
                },
            }),
        )
    })

    return item
}

type Feature = {
    type: 'Feature'
    geometry?: { coordinates?: number[] }
    properties?: { name?: string }
}

function parseValue(value?: string) {
    try {
        return value ? JSON.parse(value) : null
    } catch {
        return null
    }
}

/** Mirrors widgets/rz_geotag_field.html.twig. */
export function rzGeotagRenderer(args: RzGeotagOptions) {
    const fieldId = args.id
    const wrapperId = `${fieldId}-wrapper`
    const componentId = `${fieldId}-component`
    const searchId = `${fieldId}-search-value`
    const itemDetailIdPrefix = `${fieldId}-item-`
    const geo = parseValue(args.value)

    const wrapper = document.createElement('rz-form-field')
    wrapper.id = wrapperId

    const head = rzFormFieldHeadRenderer({
        label: args.label,
        iconClass: 'rz-icon-ri--compass-3-line',
    })
    wrapper.appendChild(head)

    const headEnd = document.createElement('div')
    headEnd.className =
        'rz-button-group rz-button-group--md rz-form-field__head__end'
    head.appendChild(headEnd)

    const search = document.createElement('input', { is: 'rz-input' })
    search.setAttribute('is', 'rz-input')
    search.name = 'location_search'
    search.id = searchId
    search.type = 'search'
    search.placeholder = 'Search a location'
    search.setAttribute('form', '')
    if (geo?.type === 'Feature') search.value = geo.properties?.name || ''
    headEnd.appendChild(search)

    headEnd.appendChild(
        rzButtonRenderer({
            size: 'sm',
            iconClass: 'rz-icon-ri--search-line',
            attributes: {
                class: 'rz-form-field__head__button',
                type: 'button',
                id: `${fieldId}-search`,
                title: 'Search location',
                commandfor: componentId,
                command: '--search-location',
            },
        }),
    )
    headEnd.appendChild(
        rzButtonRenderer({
            size: 'sm',
            color: 'danger',
            emphasis: 'tertiary',
            iconClass: 'rz-icon-ri--delete-bin-7-line',
            attributes: {
                class: 'rz-form-field__head__button',
                type: 'button',
                id: `${fieldId}-delete`,
                title: args.multiple
                    ? 'Delete all locations'
                    : 'Delete location',
                commandfor: componentId,
                command: args.multiple
                    ? '--delete-all-marker'
                    : '--delete-marker',
            },
        }),
    )

    if (args.help) {
        // rz_form_field.help (macros/rz_form_field.html.twig)
        const help = document.createElement('p')
        help.id = `${fieldId}_help`
        help.classList.add('rz-form-field__description')
        help.textContent = args.help
        wrapper.appendChild(help)
    }

    const template = document.createElement('template')
    template.setAttribute('data-item-detail', '')
    template.content.appendChild(itemDetailRenderer(componentId))
    wrapper.appendChild(template)

    const element = document.createElement('rz-geotag')
    element.classList.add('rz-form-field__body')
    element.id = componentId
    element.setAttribute('wrapper-id', wrapperId)
    element.setAttribute('search-input-id', searchId)
    element.setAttribute('multiple', args.multiple ? 'true' : 'false')
    element.setAttribute('item-detail-id-prefix', itemDetailIdPrefix)
    wrapper.appendChild(element)

    const textarea = document.createElement('textarea')
    textarea.classList.add(`${COMPONENT_CLASS_NAME}__textarea`)
    textarea.id = fieldId
    textarea.name = fieldId
    textarea.value = args.value || ''
    element.appendChild(textarea)

    if (args.multiple) {
        const list = document.createElement('div')
        list.classList.add(`${COMPONENT_CLASS_NAME}__item-list`)
        list.setAttribute('data-item-detail-list', '')
        if (geo?.type === 'FeatureCollection') {
            ;(geo.features as Feature[]).forEach((feature, index) => {
                const [lon, lat] = feature.geometry?.coordinates || []
                if (lon === undefined || lat === undefined) return
                list.appendChild(
                    itemDetailRenderer(
                        componentId,
                        `${itemDetailIdPrefix}${index}`,
                        feature.properties?.name,
                        `${lat}, ${lon}`,
                    ),
                )
            })
        }
        element.appendChild(list)
    }

    return wrapper
}
