class GeoCodingService {
  async geoCode(query) {
    const response = await fetch(
      'https://nominatim.openstreetmap.org/search?'
      + new URLSearchParams({
        format: 'json',
        q: query,
      }),
      {
        method: 'GET',
        headers: {
          Accept: 'application/json',
        },
        credentials: 'omit',
      },
    )

    const data = await response.json()
    if (data && data[0]) {
      return data[0]
    }

    return null
  }
}

export default new GeoCodingService()
