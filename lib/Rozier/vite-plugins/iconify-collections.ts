import { promises as fs } from 'fs';
import path from 'path';
import { getIconSet } from '../app/utils/icons/create-icon-set.js'
import { getIconsCSS} from '@iconify/utils';
import { locate } from '@iconify/json';
import { IconSet } from '@iconify/tools';

type IconifyCollectionOptions =  {
	outputDir?: string
}

type IconifyCollectionConfig = {
	prefix: string // Iconify prefix key or custom if srcDir is provided
	srcDir?: string // Folder with SVG files
	icons?: string[] // Available icon names from iconify collection
	outputName?: string
}

export default function iconifyCollectionsPlugin(collections: IconifyCollectionConfig[], options?: IconifyCollectionOptions) {
  return {
    name: 'vite-plugin-iconify-collections',
    async buildStart() {
		const outputDir = options?.outputDir || 'app/assets/vendors'

		// Arrays to store the paths of the generated files
		const cssFilePaths: string[] = []

		const promises = collections.map(async (collection, index) => {
			const {
				prefix = '',
				srcDir = 'app/assets/img/icons',
				icons = [],
			} = collection
			const outputName = collection.outputName || `${prefix}-icon-collection`

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

			// Filter available icon by provided name
			if(icons?.length) {
				iconSet.list().forEach(name => {
					if(!icons.includes(name)) iconSet.remove(name)
				})
			}

			const json = iconSet.export(true); // true = optimization for Iconify
			await fs.mkdir(outputDir, { recursive: true });
			const outputPath = path.resolve(process.cwd(), outputDir);

			// Export CSS
			const css = getIconsCSS(json, iconSet.list(), {
				format: 'compressed',
				iconSelector: '.{prefix}-icon--{name}',
				commonSelector: '.{prefix}-icon',
			});

			const cssFileName = `${outputName}.css`
			const cssFile = path.join(outputPath, cssFileName);
			await fs.writeFile(cssFile, css, 'utf-8');
			cssFilePaths.push(`./${cssFileName}`)

			this.info(`✅ CSS collection generate : ${cssFile}`);
		})

		await Promise.all(promises);

		// Generate css index file
		const cssIndexPath = path.join(outputDir, 'index.css')
		const cssContent = cssFilePaths
				.map(file => `@import '${file}';`)
				.join('\n')
		await fs.writeFile(cssIndexPath, cssContent, 'utf-8');
		this.info(`✅ Index CSS generate: ${cssIndexPath}`);
    },
  };
}
