import { get } from '~/utils/http'

/**
 * Fetch provider items from an array of ids.
 *
 * @param {Array} ids
 * @param {Object} filters
 * @returns {Promise<Object|null>}
 */
export async function getItemsByIds({ ids = [], filters }) {
    const data = await get('providerAjaxByArray', {
        query: {
            providerClass: filters?.providerClass,
            _locale: filters?._locale || undefined,
            ids,
        },
    })

    if (!data?.items) return null

    return { items: data.items }
}

/**
 * Fetch provider items from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Boolean} moreData
 * @returns {Promise<Object>}
 */
export async function getItems({ searchTerms, preFilters, filters, moreData }) {
    const data = await get('providerAjaxExplorer', {
        query: {
            _action: 'toggleExplorer',
            search: searchTerms,
            page: moreData && filters ? filters.nextPage : 1,
            _locale: filters?._locale || preFilters?._locale || undefined,
            providerClass: preFilters?.providerClass || undefined,
            options: preFilters?.providerOptions || undefined,
        },
    })

    if (!data?.entities) return {}

    return { items: data.entities, filters: data.filters }
}
