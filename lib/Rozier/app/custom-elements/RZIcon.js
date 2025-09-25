import { getIconCSS, getIconData, iconToSVG, iconToHTML, replaceIDs } from '@iconify/utils';
import collections from '~/utils/icons/collections.js'

// TODO:
// see usage with only class name

export default class RZIcon extends HTMLElement {
	constructor() {
	    super();
    	this.attachShadow({ mode: 'open' });
  	}

	static get observedAttributes() {
		return ['icon', 'width', 'height', 'color', 'mode', 'prefix'];
	}

	iconName = null;
	width = null;
	height = null;
	color = null;
	mode = 'svg'; // svg | class
	prefix = 'ri' // ri -> remix | custom
	collection = collections[this.prefix]

	connectedCallback() {
		this.render();
	}

  	attributeChangedCallback(name, oldValue, newValue) {
    	if (oldValue === newValue) return

		if (name === 'icon') this.iconName = newValue;
		if (name === 'width') this.width = newValue;
		if (name === 'height') this.height = newValue;
		if (name === 'color') this.color = newValue;
		if (name === 'mode') this.mode = newValue;
		if (name === 'prefix') this.prefix = newValue;

		this.render();
	}

	render() {
		if (!this.shadowRoot || !this.iconName) return;
		this.shadowRoot.innerHTML = '';

		const iconData = getIconData(this.collection, this.iconName);

		if(!iconData) {
			return
		}

		if (this.mode === 'svg') {
			const renderData = iconToSVG(iconData);
			const svg = iconToHTML(replaceIDs(renderData.body), { ...renderData.attributes, color: this.color });

			this.shadowRoot.innerHTML = svg || `<span>❌</span>`;
		} else {
			const iconClass = 'icon'

			const iconCss = getIconCSS({
				...iconData,
				width: this.width || '24px',
				height: this.height || '24px',
			}, {
				iconSelector: `.${iconClass}`,
				rules: {
					color: this.color,
				}
			})

			this.shadowRoot.innerHTML = `<style>${iconCss}</style><span class="${iconClass}"></span`;
		}
	}
}
