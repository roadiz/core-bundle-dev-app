import { get } from '~/utils/http'

async function fetchTags(query) {
    const data = await get('tagsAjaxExplorer', {
        query: { _action: 'tagsExplorer', ...query },
    })

    if (!data?.tags) return {}

    return { items: data.tags }
}

/**
 * Fetch tags.
 *
 * @return {Promise<Object>}
 */
export function getTags() {
    return fetchTags({})
}

/**
 * Fetch parent tags only.
 *
 * @return {Promise<Object>}
 */
export function getParentTags() {
    return fetchTags({ onlyParents: true })
}
