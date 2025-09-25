import { toKebabCase } from '~/utils/string/toKebabCase'
import { parseSVGContent } from '@iconify/utils';

/** @function
 * @name createCollection
 * Create an iconify collection from a list of svg files
 *
 * @param {Object} modules - return type of import.meta.glob: { path: () => Promise<string> }
 * @param {Object} attributes - keys: prefix, aliases, lastModified, width, height
 *
 * */

async function createCollection(modules, attributes = {}) {
	const collection = {
		icons: {},
		...attributes,
	}

	Object.entries(modules).forEach(async ([path, importFunction]) => {
		const fileName = path.match(/\/([^/]+)\.(svg)$/)?.[1] // MyElement or my-element
		const iconName = toKebabCase(fileName)

		const content = (await importFunction()).default
		Object.assign(collection.icons, {
			[iconName]: {
				body: parseSVGContent(content)?.body
			}
		})
	})

	return collection
}

export { createCollection }
