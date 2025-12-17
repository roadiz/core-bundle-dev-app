import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
// import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'
import { rzElement, type RzElement } from '~/utils/component-renderer/rzElement'

type PossibleElement =
    | (RzElement & { children?: PossibleElement[] })
    | HTMLElement

export type Args = RzElement & {
    elements: PossibleElement[]
}

const meta: Meta<Args> = {
    title: 'Components/ListingMenu',
    tags: ['autodocs'],
    args: {
        tag: 'form',
        attributes: {
            method: 'get',
        },
        elements: [
            {
                tag: 'div',
                attributes: {
                    class: 'rz-listing-menu__group rz-listing-menu__group--divider',
                },
                children: [
                    {
                        tag: 'label',
                        attributes: {
                            'aria-label': 'Grid view',
                            class: 'rz-button--secondary',
                        },
                        innerHTML: `
							<input id="listing-layout-grid" type="radio" name="layout" value="grid" />
							<span aria-hidden="true" class="rz-icon-ri--layout-grid-line"></span>
							`,
                    },
                    {
                        tag: 'label',
                        attributes: {
                            'aria-label': 'List view',
                            class: 'rz-button--secondary',
                        },
                        innerHTML: `
							<input id="listing-layout-list" type="radio" name="layout" value="list" />
							<span aria-hidden="true" class="rz-icon-ri--list-check"></span>
							`,
                    },
                ],
            },
            {
                tag: 'input',
                is: 'rz-input',
                attributes: {
                    name: 'search',
                    type: 'search',
                    placeholder: 'Search...',
                    class: 'rz-listing-menu__search-input',
                },
            },
            {
                tag: 'select',
                is: 'rz-select',
                attributes: {
                    name: 'embedPlatform',
                },
                innerHTML: `
                        <option label="-- Tag --"value=""></option>
                        <option label="Tag 1" value="tag-1"></option>
						<option label="Tag 2" value="tag-2"></option>
				`,
            },
            {
                tag: 'select',
                is: 'rz-select',
                attributes: {
                    name: 'embedPlatform',
                },
                innerHTML: `
                        <option label="-- Embedded Media --" value=""></option>
                        <option label="Unsplash" value="unsplash"></option>
						<option label="Apple Podcast" value="apple_podcast"></option>
						<option label="Dailymotion" value="dailymotion"></option>
						<option label="Deezer" value="deezer"></option>
						<option label="Mixcloud" value="mixcloud"></option>
						<option label="Podcast" value="podcast"></option>
						<option label="Soundcloud" value="soundcloud"></option>
						<option label="Spotify" value="spotify"></option>
						<option label="Ted" value="ted"></option>
						<option label="Vimeo" value="vimeo"></option>
						<option label="Youtube" value="youtube"></option>
				`,
            },
            rzButtonRenderer({
                attributes: { type: 'submit' },
                label: 'Apply Filters',
                iconClass: 'rz-icon-ri--check-line',
            }),
            {
                tag: 'div',
                attributes: { class: 'rz-listing-menu__group' },
                children: [
                    {
                        tag: 'span',
                        innerText: 'Page',
                        attributes: { class: 'text-form-label' },
                    },
                    {
                        tag: 'input',
                        attributes: {
                            class: 'rz-input',
                            type: 'number',
                            name: 'page',
                            value: '1',
                            min: '1',
                            max: '999',
                            'aria-label': 'Page number',
                        },
                    },
                    {
                        tag: 'span',
                        innerText: '/ Page length',
                        attributes: { class: 'text-form-label' },
                    },
                    rzButtonRenderer({
                        attributes: {
                            type: 'button',
                            'aria-label': 'Previous page',
                        },
                        iconClass: 'rz-icon-ri--arrow-left-s-line',
                    }),
                    rzButtonRenderer({
                        attributes: {
                            type: 'button',
                            'aria-label': 'Next page',
                        },
                        iconClass: 'rz-icon-ri--arrow-right-s-line',
                    }),
                    {
                        tag: 'select',
                        is: 'rz-select',
                        attributes: {
                            name: 'item_per_page',
                        },
                        innerHTML: `
							<option label="10 items" selected value="10"></option>
							<option label="20 items" value="20"></option>
							<option label="50 items" value="50"></option>
							<option label="100 items" value="100"></option>
							<option label="200 items" value="200"></option>
				`,
                    },
                ],
            },
        ],
    },
}

export default meta
type Story = StoryObj<Args>

function appendElements(elements: Args['elements'], parent: HTMLElement) {
    elements.map((element) => {
        if (element instanceof HTMLElement) {
            parent.appendChild(element)
        } else {
            const node = rzElement(element)
            if (element.children) {
                appendElements(element.children, node)
            }
            parent.appendChild(node)
        }
    })
}

function rzListingMenuRenderer(args: Args) {
    const root = rzElement(args)
    root.classList.add('rz-listing-menu')

    appendElements(args.elements, root)
    return root
}

export const Default: Story = {
    render: (args) => {
        return rzListingMenuRenderer(args)
    },
}
