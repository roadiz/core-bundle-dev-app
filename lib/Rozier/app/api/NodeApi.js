import { get } from '~/utils/http'

/**
 * Fetch Nodes from an array of node id.
 *
 * @param {Array} ids
 * @param {Object} filters
 * @returns {Promise<Object|null>}
 */
export async function getNodesByIds({ ids = [], filters = {} }) {
    const data = await get('nodesAjaxByArray', {
        query: {
            _action: 'nodesByIds',
            _locale: filters?._locale || undefined,
            ids,
        },
    })

    if (!data?.items) return null

    return { items: data.items }
}

/**
 * Fetch Nodes from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @returns {Promise<Object>}
 */
export async function getNodes({
    searchTerms,
    preFilters,
    filters,
    filterExplorerSelection,
    moreData,
}) {
    const nodeTypes = preFilters?.nodeTypes
        ? JSON.parse(preFilters.nodeTypes)
        : null

    const data = await get('nodesAjaxExplorer', {
        query: {
            _action: 'toggleExplorer',
            search: searchTerms,
            page: moreData && filters ? filters.nextPage : 1,
            _locale: filters?._locale || preFilters?._locale || undefined,
            tagId: filterExplorerSelection?.id || undefined,
            nodeTypes:
                Array.isArray(nodeTypes) && nodeTypes.length
                    ? nodeTypes
                    : undefined,
        },
    })

    if (!data?.nodes) return {}

    return { items: data.nodes, filters: data.filters }
}
