import { get } from '~/utils/http'

/**
 * Fetch documents Folders.
 *
 * @return {Promise<Object>}
 */
export async function getFolders() {
    const data = await get('foldersAjaxExplorer', {
        query: { _action: 'foldersExplorer' },
    })

    if (!data?.folders) return {}

    return { items: data.folders }
}
