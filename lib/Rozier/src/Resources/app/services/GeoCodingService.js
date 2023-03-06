import request from 'axios'

class GeoCodingService {
    geoCode(query) {
        return request({
            method: 'GET',
            url: 'https://nominatim.openstreetmap.org/search',
            params: {
                format: 'json',
                q: query,
            },
        })
            .then((response) => {
                if (typeof response.data !== 'undefined' && response.data.length > 0) {
                    return response.data[0]
                } else {
                    return null
                }
            })
            .catch((error) => {
                throw new Error(error)
            })
    }
}

export default new GeoCodingService()
