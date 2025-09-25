import { getIconData, iconToSVG, iconToHTML, replaceIDs } from '@iconify/utils';
import collections from '~/assets/vendors/iconify/index.js'

export default class RZIcon extends HTMLElement {
	constructor() {
	    super();
  	}

	getCollection() {
		return collections.find(collection => collection.prefix === this.prefix)
	}

	mode = 'css'; // svg || class
	prefix = 'ri' // prefix setup with iconifyCollectionPlugin in vite.config.js
	name = null;
	width = null;
	height = null;
	color = null;
	collection = this.getCollection()

	static get observedAttributes() {
		return ['name', 'width', 'height', 'color', 'mode', 'prefix'];
	}

  	attributeChangedCallback(attribut, oldValue, newValue) {
    	if (oldValue === newValue) return

		if (attribut === 'name') this.name = newValue;
		if (attribut === 'width') this.width = newValue;
		if (attribut === 'height') this.height = newValue;
		if (attribut === 'color') this.color = newValue;
		if (attribut === 'mode') this.mode = newValue;
		if (attribut === 'prefix') this.prefix = newValue;

		if(attribut === 'prefix' || attribut === 'name') {
			this.collection = this.getCollection()
		}

		this.render();
	}

	connectedCallback() {
		this.render();
	}

	render() {
		if (!this.collection || !this.name) {
			this.innerHTML = '';
			return;
		}

		if (this.mode === 'svg') {
			const iconData = getIconData(this.collection, this.name);
			const renderData = iconToSVG(iconData);

			if (!renderData?.body) {
				this.innerHTML = `�`;
				return
			}

			this.innerHTML = iconToHTML(
				replaceIDs(renderData.body),
				{ ...renderData.attributes, color: this.color }
			);

		} else {
			// TODO: Unify code from class generation in iconify-collections.ts
			this.classList.add(`${this.prefix}-icon`, `${this.prefix}-icon--${this.name}`)

			if(this.color) this.style.color = this.color
		}
	}
}
