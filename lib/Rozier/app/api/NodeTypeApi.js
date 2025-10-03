/**
 * Fetch NodeTypes from an array of node name.
 *
 * @param {Array} ids
 * @returns {Promise<R>|Promise.<T>}
 */
export function getNodeTypesByIds({ ids = [] }) {
  // Trim ids
  ids = ids.map(item => item.trim())

  const postData = {
    _token: window.RozierConfig.ajaxToken,
    _action: 'nodeTypesByIds',
  }
  /*
     * We need to send the ids as an object with keys as string
     * when Varnish is enabled, the query string is sorted
     */
  for (let i = 0; i < ids.length; i++) {
    postData['names[' + i + ']'] = ids[i]
  }

  return fetch(window.RozierConfig.routes.nodeTypesAjaxByArray + '?' + new URLSearchParams(postData), {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      // Required to prevent using this route as referer when login again
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then(async (response) => {
      const data = await response.json()
      if (typeof data !== 'undefined' && data.items) {
        return {
          items: data.items,
        }
      }
      else {
        return null
      }
    })
    .catch(async (error) => {
      throw new Error((await error.response.json()).humanMessage)
    })
}

/**
 * Fetch NodeTypes from search terms.
 *
 * @param {String} searchTerms
 * @param {Object} preFilters
 * @param {Object} filters
 * @param {Object} filterExplorerSelection
 * @param {Boolean} moreData
 * @returns {Promise.<T>|Promise<R>}
 */
export function getNodeTypes({ searchTerms, filters, moreData }) {
  const postData = {
    _token: window.RozierConfig.ajaxToken,
    _action: 'toggleExplorer',
    search: searchTerms,
    page: 1,
  }

  if (moreData) {
    postData.page = filters ? filters.nextPage : 1
  }

  return fetch(window.RozierConfig.routes.nodeTypesAjaxExplorer + '?' + new URLSearchParams(postData), {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      // Required to prevent using this route as referer when login again
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then(async (response) => {
      const data = await response.json()
      if (typeof data !== 'undefined' && data.nodeTypes) {
        return {
          items: data.nodeTypes,
          filters: data.filters,
        }
      }
      else {
        return {}
      }
    })
    .catch(async (error) => {
      throw new Error((await error.response.json()).humanMessage)
    })
}
