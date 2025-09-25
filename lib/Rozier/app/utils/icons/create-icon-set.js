import { importDirectory } from '@iconify/tools';
import { cleanupSVG, parseColors, runSVGO } from '@iconify/tools';

export async function getIconSet(src, prefix) {
	  const iconSet = await importDirectory(src, {
		prefix
	  });

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

		// replace in the set
		iconSet.fromSVG(name, svg);
	  }

	  return iconSet
}
