import { get, post } from '~/utils/http'

/**
 * Fetch Documents from an array of document id.
 *
 * @param {Array} ids
 * @param {Object} filters
 * @returns {Promise<Object|null>}
 */
export async function getDocumentsByIds({ ids = [], filters = {} }) {
    const data = await get('documentsAjaxByArray', {
        query: {
            _action: 'documentsByIds',
            _locale: filters?._locale || undefined,
            ids,
        },
    })

    if (!data?.documents) return null

    return { items: data.documents, trans: data.trans }
}

/**
 * Fetch Documents from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @return {Promise<Object>}
 */
export async function getDocuments({
    searchTerms,
    preFilters,
    filters,
    filterExplorerSelection,
    moreData,
}) {
    const data = await get('documentsAjaxExplorer', {
        query: {
            _action: 'toggleExplorer',
            search: searchTerms,
            page: moreData && filters ? filters.nextPage : 1,
            _locale: filters?._locale || preFilters?._locale || undefined,
            folderId: filterExplorerSelection?.id || undefined,
        },
    })

    if (!data?.documents) return {}

    return { items: data.documents, filters: data.filters, trans: data.trans }
}

/**
 * Post the document edit form of the current page.
 *
 * @param {FormData} formData whole form, carrying its own CSRF field
 * @returns {Promise<Object>}
 */
export function setDocument(formData) {
    return post(window.location.href, { body: formData, csrfField: null })
}
