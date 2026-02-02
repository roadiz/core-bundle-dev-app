import {
    DOCUMENT_ENTITY,
    NODE_ENTITY,
    NODE_TYPE_ENTITY,
    TAG_ENTITY,
} from '../types/entityTypes'

// Components
import DocumentPreviewListItem from '../components/DocumentPreviewListItem.vue'
import JoinPreviewItem from '../components/JoinPreviewItem.vue'

// Containers
import TagCreatorContainer from '../containers/TagCreatorContainer.vue'

export default class EntityAwareFactory {
    static getState(entity) {
        const result = {
            trans: {
                moreItems: '',
            },
        }

        // Default
        result.isFilterEnable = false
        result.currentListingView = JoinPreviewItem
        result.trans.moreItems = 'moreEntities'

        switch (entity) {
            case DOCUMENT_ENTITY:
                result.currentListingView = DocumentPreviewListItem
                result.filterExplorerIcon = 'rz-icon-ri--folders-line'
                result.trans.moreItems = 'moreDocuments'
                result.isFilterEnable = true
                break
            case NODE_ENTITY:
                result.filterExplorerIcon = 'rz-icon-ri--price-tag-3-line'
                result.trans.moreItems = 'moreNodes'
                result.isFilterEnable = true
                break
            case NODE_TYPE_ENTITY:
                result.trans.moreItems = 'moreNodeTypes'
                break
            case TAG_ENTITY:
                result.trans.moreItems = 'moreTags'
                result.isFilterEnable = true
                result.filterExplorerIcon = 'rz-icon-ri--price-tag-3-line'
                break
            default:
                result.trans.moreItems = 'moreEntities'
                break
        }

        return result
    }

    static getListingView(entity) {
        switch (entity) {
            case DOCUMENT_ENTITY:
                return DocumentPreviewListItem
            default:
                return JoinPreviewItem
        }
    }

    static getWidgetView(entity) {
        switch (entity) {
            case TAG_ENTITY:
                return TagCreatorContainer
            default:
                return null
        }
    }
}
