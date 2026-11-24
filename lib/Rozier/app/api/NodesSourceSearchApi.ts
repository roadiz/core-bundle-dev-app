import type { NodeSourceSearch } from '~/types/node-source-search'
import { get } from '~/utils/http'

export async function getNodesSourceFromSearch(searchTerms: string) {
    const data = await get<{ data?: NodeSourceSearch[] }>('searchAjax', {
        query: { _action: 'searchNodesSources', searchTerms },
    })

    return data?.data?.length ? data.data : []
}
