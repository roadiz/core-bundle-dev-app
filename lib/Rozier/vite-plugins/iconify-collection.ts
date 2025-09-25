import { promises as fs } from 'fs';
import path from 'path';
import { getIconSet } from '../app/utils/icons/create-icon-set.js'
import { getIconsCSS} from '@iconify/utils';


type IconifyCollectionPluginOptions = {
	srcDir: string
	outputDir: string
	prefix: string
	outputName: string
}

export default function iconifyCollectionPlugin(options: IconifyCollectionPluginOptions) {
  const {
    srcDir = 'app/assets/icons',
    outputDir = 'app/assets/',
    prefix = 'custom',
	outputName = `prefix-colleciton`
  } = options;

  return {
    name: 'vite-plugin-iconify-collection',
    async buildStart() {
		const srcPath = path.resolve(process.cwd(), srcDir);

		this.info(`Generate ${prefix} collection Iconify from ${srcPath}...`);
		const iconSet = await getIconSet(srcPath, prefix);

		// Export JSON
		const outputPath = path.resolve(process.cwd(), outputDir);
		const json = iconSet.export(true); // true = optimization for Iconify
		const file = path.join(outputPath, `${outputName}.json`);

		await fs.mkdir(outputPath, { recursive: true });
		await fs.writeFile(file, JSON.stringify(json, null, 2), 'utf-8');
		this.info(`✅ Json collection generate: ${file}`);

		// Export CSS
		const names = iconSet.list();
		const css = getIconsCSS(json, names, {
			format: 'expanded',
			iconSelector: '.icon--{prefix}--{name}',
			commonSelector: '.icon--{prefix}',
		});

		const cssFile = path.join(outputPath, `${outputName}.css`);
		await fs.writeFile(cssFile, css, 'utf-8');
		this.info(`✅ CSS collection generate : ${cssFile}`);
    },
  };
}
