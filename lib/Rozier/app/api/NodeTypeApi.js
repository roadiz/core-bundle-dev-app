import { get } from '~/utils/http'

/**
 * Fetch NodeTypes from an array of node type names.
 *
 * @param {Array} ids
 * @returns {Promise<Object|null>}
 */
export async function getNodeTypesByIds({ ids = [] }) {
    const data = await get('nodeTypesAjaxByArray', {
        query: {
            _action: 'nodeTypesByIds',
            names: ids.map((item) => item.trim()),
        },
    })

    if (!data?.items) return null

    return { items: data.items }
}

/**
 * Fetch NodeTypes from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} filters
 * @param {Boolean} moreData
 * @returns {Promise<Object>}
 */
export async function getNodeTypes({ searchTerms, filters, moreData }) {
    const data = await get('nodeTypesAjaxExplorer', {
        query: {
            _action: 'toggleExplorer',
            search: searchTerms,
            page: moreData && filters ? filters.nextPage : 1,
        },
    })

    if (!data?.nodeTypes) return {}

    return { items: data.nodeTypes, filters: data.filters }
}
