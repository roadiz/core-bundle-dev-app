import { get } from '~/utils/http'

/**
 * Fetch Joins from an array of ids.
 *
 * @param {Array} ids
 * @param {Object} filters
 * @returns {Promise<Object|null>}
 */
export async function getJoinsByIds({ ids = [], filters }) {
    const data = await get('joinsAjaxByArray', {
        query: {
            nodeTypeFieldName: filters?.nodeTypeField,
            nodeTypeName: filters?.nodeTypeName,
            _locale: filters?._locale || undefined,
            ids,
        },
    })

    if (!data?.items) return null

    return { items: data.items }
}

/**
 * Fetch Joins from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Boolean} moreData
 * @returns {Promise<Object>}
 */
export async function getJoins({ searchTerms, preFilters, filters, moreData }) {
    const data = await get('joinsAjaxExplorer', {
        query: {
            _action: 'toggleExplorer',
            nodeTypeName: preFilters?.nodeTypeName,
            search: searchTerms,
            page: moreData && filters ? filters.nextPage : 1,
            _locale: filters?._locale || preFilters?._locale || undefined,
            nodeTypeFieldName: preFilters?.nodeTypeField || undefined,
        },
    })

    if (!data?.entities) return {}

    return { items: data.entities, filters: data.filters }
}
