import * as NodesSourceSearchApi from './NodesSourceSearchApi'
import * as ExplorerApi from './ExplorerApi'
import * as FilterExplorerApi from './FilterExplorerApi'
import * as DrawerApi from './DrawerApi'
import * as TagApi from './TagApi'
import * as ExplorerProviderApi from './ExplorerProviderApi'

const api = {
    ...NodesSourceSearchApi,
    ...ExplorerApi,
    ...FilterExplorerApi,
    ...DrawerApi,
    ...TagApi,
    ...ExplorerProviderApi,
}

export default api
