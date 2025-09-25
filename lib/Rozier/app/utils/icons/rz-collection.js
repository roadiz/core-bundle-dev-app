import { createCollection } from '~/utils/icons/create-collection.js'

const modules = import.meta.glob(`~/assets/img/rz-icons/*.svg`, {
	query: '?raw',
})

const RZCollection = await createCollection(modules, { prefix: 'rz', width: 24, height: 24 })

export { RZCollection }
