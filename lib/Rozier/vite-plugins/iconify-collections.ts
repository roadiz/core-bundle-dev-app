import { promises as fs } from 'fs';
import path from 'path';
import { getIconSet } from '../app/utils/icons/create-icon-set.js'
import { getIconsCSS} from '@iconify/utils';
import { locate } from '@iconify/json';
import { IconSet } from '@iconify/tools';

type IconifyCollectionConfig = {
	prefix: string // Iconify prefix key or custom if srcDir is used
	srcDir?: string // Folder with SVG files
	outputName?: string
	outputDir?: string
}

export default function iconifyCollectionsPlugin(collections: IconifyCollectionConfig[]) {
  return {
    name: 'vite-plugin-iconify-collections',
    async buildStart() {

		// Arrays to store the paths of the generated files
		const cssFilePaths: string[] = []
		const jsonFilePaths: string[] = []

		const promises = collections.map(async (collection, index) => {
			const {
				prefix = '',
				srcDir = 'app/assets/img/icons',
				outputDir = 'app/assets',
				outputName = `icon-collection-${index}`,
			} = collection

			// get iconSet from module file or from custom SVG collection folder
			let iconSet = null
			try {
				const iconifyFilePath = locate(prefix);
				const file = await fs.readFile(iconifyFilePath, 'utf8')
				iconSet = new IconSet(JSON.parse(file));

			} catch {
				if (srcDir) {
					const srcPath = path.resolve(process.cwd(), srcDir);
					iconSet = await getIconSet(srcPath, prefix);
				}

				if (!iconSet) {
					this.warm(`Can't find json collection for: ${prefix}`);
					return
				}
			}

			const json = iconSet.export(true); // true = optimization for Iconify
			const fileName = outputName || `${iconSet.prefix}-icon-collection`

			// Export JSON
			const outputPath = path.resolve(process.cwd(), outputDir || 'app/assets/vendors');
			await fs.mkdir(outputPath, { recursive: true });
			const jsonFileName = `${fileName}.json`
			const filePath = path.join(outputPath, jsonFileName);
			await fs.writeFile(filePath, JSON.stringify(json, null, 2), 'utf-8');
			jsonFilePaths.push(`./${jsonFileName}`)
			this.info(`✅ Json collection generate: ${filePath}`);

			// Export CSS
			const css = getIconsCSS(json, iconSet.list(), {
				format: 'compressed',
				iconSelector: '.{prefix}-icon--{name}',
				commonSelector: '.{prefix}-icon',
			});

			const cssFileName = `${fileName}.css`
			const cssFile = path.join(outputPath, cssFileName);
			await fs.writeFile(cssFile, css, 'utf-8');
			cssFilePaths.push(`./${cssFileName}`)

			this.info(`✅ CSS collection generate : ${cssFile}`);
		})

		await Promise.all(promises);

		// Generate index file (js & css)
		const outputDir = path.resolve(process.cwd(), collections[0]?.outputDir || 'app/assets/vendors');

		// GENERATE index.css
		const cssContent = cssFilePaths
				.map(file => `@import '${file}';`)
				.join('\n')

		await fs.writeFile(
			path.join(outputDir, 'index.css'),
			cssContent,
			'utf-8'
		);


		// 2. GÉNÉRATION DE index.js
		const jsonImports = jsonFilePaths
			.map((file, i) => `import c${i} from '${file}';`)
			.join('\n');

		const jsonExports = jsonFilePaths
			.map((file, i) => `c${i}`)
			.join(',\n  ');

		const jsContent = `
// Automatically generated file by vite-plugin-iconify-collection
${jsonImports}

/**
 * Exports an array of all generated Iconify JSON collections.
 * * Ex: import iconCollections from './index.js';
 */
export default [
${jsonExports}
];
		`.trim();

		const indexJsPath = path.join(outputDir, 'index.js');
		await fs.writeFile(indexJsPath, jsContent, 'utf-8');
		this.info(`✅ Index JS generate: ${indexJsPath}`);
    },
  };
}
