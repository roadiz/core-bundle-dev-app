export interface NominatimSearchResult {
    lat: string
    lon: string
    display_name: string
    place_id?: number
    boundingbox?: [string, string, string, string]
    class?: string
    type?: string
    importance?: number
    icon?: string
}

export async function nominatimFetch(
    query: string,
): Promise<NominatimSearchResult | null> {
    const url =
        'https://nominatim.openstreetmap.org/search?' +
        new URLSearchParams({
            format: 'json',
            q: query,
        }).toString()

    const response = await fetch(url, {
        method: 'GET',
        headers: {
            Accept: 'application/json',
        },
        credentials: 'omit',
    })

    const data = (await response.json()) as NominatimSearchResult[]
    if (data && data[0]) {
        return data[0]
    }

    return null
}
