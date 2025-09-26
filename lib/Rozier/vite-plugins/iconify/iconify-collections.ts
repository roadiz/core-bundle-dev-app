import { promises as fs } from 'fs';
import path from 'path';
import { getIconsCSS} from '@iconify/utils';
import { locate } from '@iconify/json';
import { IconSet } from '@iconify/tools';
import { importDirectory } from '@iconify/tools';
import { cleanupSVG, parseColors, runSVGO } from '@iconify/tools';


type IconifyCollectionOptions =  {
	outputDir?: string
}

type IconifyCollectionConfig = {
	prefix: string // Iconify prefix key or custom if srcDir is provided
	srcDir?: string // Folder with SVG files
	icons?: string[] // Available icon names from iconify collection
	outputName?: string
}


async function getIconSet(path: string, prefix: string) {
	  const iconSet = await importDirectory(path, { prefix});

		// clean & optimisation
	  for (const name of iconSet.list()) {
		const svg = iconSet.toSVG(name);

		if (!svg) {
		  console.warn(`Error on ${name}`);
		  continue;
		}

		await cleanupSVG(svg);
		await parseColors(svg, { defaultColor: 'currentColor' });
		await runSVGO(svg);

		iconSet.fromSVG(name, svg);
	  }

	  return iconSet
}


async function generateIconNameFile(prefix: string, names: string[]) {
	const iconsListPath = path.resolve(process.cwd(), `vite-plugins/iconify/${prefix}-icons.ts`)
	const jsContent =
`
/**
 * Generated file by vite-plugins/iconify-collections-plugin.ts
 * Exports all available icon names from the '${prefix}' iconify collection.
 * Names are typically bound from SVG file names when using the 'srcDir' option.
 */
export default [
	'${names.map((name) => `${name}`).join('\', \'')}'
];
`

	await fs.writeFile( iconsListPath, jsContent, 'utf-8');
}

export default function iconifyCollectionsPlugin(collections: IconifyCollectionConfig[], options?: IconifyCollectionOptions) {
  return {
    name: 'vite-plugin-iconify-collections',
    async buildStart() {
		const outputDir = options?.outputDir || 'app/assets/vendors'

		// Array to store paths for the index file generation
		const cssFilePaths: string[] = []

		const promises = collections.map(async (collection, index) => {
			const {
				prefix = '',
				srcDir = 'app/assets/img/icons',
				icons = [],
			} = collection
			const outputName = collection.outputName || `iconify-${prefix}-collection`

			 // --- Icon Set Loading ---
			let iconSet: IconSet | null = null

			// Try to load the official Iconify JSON package
			try {
				const iconifyFilePath = locate(prefix);
				const file = await fs.readFile(iconifyFilePath, 'utf8')
				iconSet = new IconSet(JSON.parse(file));

			} catch {
				// If official package fails, try loading from the custom SVG source directory
				if (srcDir) {
					const srcPath = path.resolve(process.cwd(), srcDir);
					iconSet = await getIconSet(srcPath, prefix);

                    // Generate a list of available icon names (useful for local collections)
					await generateIconNameFile(prefix, iconSet.list())
				}

				if (!iconSet) {
					this.warn(`Can't find json collection for: ${prefix}`);
					return
				}
			}

            // Filter the icon set to include only the requested names, if any were provided.
			if(icons?.length) {
				iconSet.list().forEach(name => {
					if(!icons.includes(name)) iconSet.remove(name)
				})
			}

			const json = iconSet.export(true); // true = optimization for Iconify
			await fs.mkdir(outputDir, { recursive: true });
			const outputPath = path.resolve(process.cwd(), outputDir);

			// Generate the optimized CSS code
			const css = getIconsCSS(json, iconSet.list(), {
				format: 'compressed',
				iconSelector: '.{prefix}-icon--{name}',
				commonSelector: '.{prefix}-icon',
			});

			const cssFileName = `${outputName}.css`
			const cssFilePath = path.join(outputPath, cssFileName);
			await fs.writeFile(cssFilePath, css, 'utf-8');
			cssFilePaths.push(`./${cssFileName}`)

			this.info(`✅ CSS collection generate : ${cssFilePath}`);
		})

		await Promise.all(promises);

        // Generate the main CSS index file that imports all collection files
		const indexCssPath = path.join(outputDir, 'index.css')
		const cssContentImports = cssFilePaths
				.map(file => `@import '${file}';`)
				.join('\n')

		const cssFileContent =
`/* File autogenerate by plugin iconify-collections.ts */
${cssContentImports}
`

		await fs.writeFile(indexCssPath, cssFileContent, 'utf-8');
		this.info(`✅ Index CSS generate: ${indexCssPath}`);
    },
  };
}
