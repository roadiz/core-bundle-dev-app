import { get, post } from '~/utils/http'

/**
 * Fetch all Tags.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @returns {Promise<Object>}
 */
export async function getTags({
    searchTerms,
    preFilters,
    filters,
    filterExplorerSelection,
    moreData,
}) {
    const data = await get('tagsAjaxExplorerList', {
        query: {
            _action: 'getTags',
            search: searchTerms,
            page: moreData && filters ? filters.nextPage : 1,
            _locale: filters?._locale || preFilters?._locale || undefined,
            tagId: filterExplorerSelection?.id || undefined,
        },
    })

    if (!data?.tags) throw new Error('No tags found')

    return { items: data.tags, filters: data.filters }
}

/**
 * Fetch Tags from an array of tag id.
 *
 * @param {Array} ids
 * @param {Object} filters
 * @returns {Promise<Object>}
 */
export async function getTagsByIds({ ids = [], filters = {} }) {
    const data = await get('tagsAjaxByArray', {
        query: {
            _action: 'documentsByIds',
            _locale: filters?._locale || undefined,
            ids,
        },
    })

    if (!data?.tags) return {}

    return { items: data.tags }
}

/**
 * Create a new tag.
 *
 * @param {String} tagName
 * @returns {Promise<Object>}
 */
export async function createTag({ tagName }) {
    const data = await post('tagsAjaxCreate', { body: { tagName } })

    if (!data?.tag) throw new Error('Tag creation error')

    return data.tag
}
