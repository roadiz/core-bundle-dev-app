import { get } from '~/utils/http'

/**
 * Fetch Custom forms from an array of ids.
 *
 * @param {Array} ids
 * @returns {Promise<Object>}
 */
export async function getCustomFormsByIds({ ids = [] }) {
    const data = await get('customFormsAjaxByArray', { query: { ids } })

    return { items: data?.forms }
}

/**
 * Fetch Custom forms from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} filters
 * @param {Boolean} moreData
 * @returns {Promise<Object>}
 */
export async function getCustomForms({ searchTerms, filters, moreData }) {
    const data = await get('customFormsAjaxExplorer', {
        query: {
            _action: 'toggleExplorer',
            search: searchTerms,
            page: moreData && filters ? filters.nextPage : 1,
        },
    })

    if (!data?.customForms) return {}

    return { items: data.customForms, filters: data.filters }
}
