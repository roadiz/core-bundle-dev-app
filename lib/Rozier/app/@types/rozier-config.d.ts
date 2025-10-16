declare global {
    interface Window {
        RozierConfig: RozierConfig
    }
}

export interface RozierConfig {
    baseUrl?: string
    resourcesUrl?: string
    ajaxToken?: string
    mainColor?: string
    mapsStyle?: unknown[]
    leafletMapTileUrl?: string
    defaultMapLocation?: unknown
    messages?: {
        login?: string
        sessionExpireTitle?: string
        sessionExpireContent?: string
        healthCheckedFailedTitle?: string
        healthCheckedFailedContent?: string
        createTag?: string
        explorer?: string
        forbiddenPage?: string
        document?: string
        documents?: string
        item?: string
        items?: string
        folder?: string
        folders?: string
        see_all?: string
        searchDocuments?: string
        searchNodes?: string
        searchCustomForms?: string
        moreDocuments?: string
        moreNodes?: string
        moreNodeTypes?: string
        moreTags?: string
        moreEntities?: string
        moreCustomForms?: string
        documentEditDialogSubmit?: string
        documentEditDialogCancel?: string
        documentEditDialogEdit?: string
        blanchetteEditor?: {
            blanchetteEditor?: string
            free?: string
            move?: string
            crop?: string
            zoomIn?: string
            zoomOut?: string
            rotateLeft?: string
            rotateRight?: string
            flipHorizontal?: string
            flipVertical?: string
            applyChange?: string
            undo?: string
            aspectRatio?: string
            saveAndOverwrite?: string
            other?: string
            landscape?: string
            portrait?: string
        }
        dropzone?: {
            maxFilesize?: number
            dictDefaultMessage?: string
            dictFallbackMessage?: string
            dictFallbackText?: string
            dictFileTooBig?: string
            dictInvalidFileType?: string
            dictResponseError?: string
            dictCancelUpload?: string
            dictCancelUploadConfirmation?: string
            dictRemoveFile?: string
            dictRemoveFileConfirmation?: null
            dictMaxFilesExceeded?: string
        }
        htmleditor?: {
            h2?: string
            h3?: string
            h4?: string
            h5?: string
            h6?: string
            fullscreen?: string
            bold?: string
            italic?: string
            strike?: string
            blockquote?: string
            link?: string
            image?: string
            listUl?: string
            listOl?: string
            back?: string
            hr?: string
            nbsp?: string
        }
        geotag?: {
            resetMarker?: string
            typeAnAddress?: string
        }
    }
    routes?: {
        ping?: string
        splashRequest?: string
        loginPage?: string
        nodeAjaxEdit?: string
        tagAjaxEdit?: string
        folderAjaxEdit?: string
        nodeTypesFieldAjaxList?: string
        customFormsFieldAjaxEdit?: string
        documentsUploadPage?: string
        documentsBulkDeletePage?: string
        documentsBulkDownloadPage?: string
        documentsAjaxExplorer?: string
        documentsAjaxByArray?: string
        customFormsAjaxByArray?: string
        nodeTypesAjaxByArray?: string
        nodeTypesAjaxExplorer?: string
        joinsAjaxByArray?: string
        nodesAjaxByArray?: string
        tagsAjaxExplorer?: string
        tagsAjaxByArray?: string
        tagsAjaxExplorerList?: string
        tagsAjaxCreate?: string
        foldersAjaxExplorer?: string
        nodesAjaxExplorer?: string
        joinsAjaxExplorer?: string
        providerAjaxExplorer?: string
        providerAjaxByArray?: string
        customFormsAjaxExplorer?: string
        searchAjax?: string
        nodesStatusesAjax?: string
        nodesTreeAjax?: string
        tagsTreeAjax?: string
        foldersTreeAjax?: string
        nodesQuickAddAjax?: string
        tagAjaxSearch?: string
        foldersAjaxSearch?: string
        ajaxSessionMessages?: string
        attributeValueAjaxEdit?: string
    }
}

export {}
