<template>
    <div v-if="document"
         :title="document.classname"
         class="rz-drawer__item"
         @click.prevent="onAddItemButtonClick">
        <div class="rz-drawer__item__asset">
            <template v-if="drawerName && entityName">
                <input type="hidden" :name="drawerName + '[' + index + '][document]'" :value="document.id" />
                <input
                    type="hidden"
                    :name="drawerName + '[' + index + '][hotspot]'"
                    :value="JSON.stringify(hotspot)"
                />
                <input
                    type="hidden"
                    :name="drawerName + '[' + index + '][imageCropAlignment]'"
                    :value="imageCropAlignment"
                />
            </template>
            <template v-else-if="drawerName">
                <input type="hidden" :name="drawerName + '[' + index + ']'" :value="document.id" />
            </template>
            <img :src="document.thumbnail80"
                 width="110"
                 height="94"
                 class="rz-drawer__item__img"
            >
        </div>
        <div v-if="!isItemExplorer" class="rz-button-group rz-button-group--sm rz-button-group--gap-sm rz-drawer__item__action">
            <button
                :id="`edit-${drawerName}[${index}]`"
                type="button"
                class="rz-button rz-button--primary"
                @click="onEditClick">
                <span class="rz-button__icon rz-icon-ri--equalizer-3-line"></span>
            </button>
            <button
                type="button"
                class="rz-button rz-button--error-light"
                @click.prevent="onRemoveItemButtonClick()">
                <span class="rz-button__icon rz-icon-ri--delete-bin-7-line"></span>
            </button>
        </div>
        <div v-if="!isItemExplorer" class="rz-button-group rz-button-group--sm rz-button-group--gap-sm rz-drawer__item__action rz-drawer__item__action--top">
            <button class="rz-button rz-button--primary" @click.prevent="onPreviewClick">
                <span class="rz-button__icon rz-icon-ri--zoom-in-line"></span>
            </button>
        </div>
        <button v-else class="rz-drawer-item__button-group rz-drawer-item__button-group--top rz-button rz-button--primary">
            <span class="rz-button__icon rz-icon-ri--add-large-line"></span>
        </button>
    </div>
</template>

<script>
import {mapActions, mapState} from 'vuex'
import filters from '../filters'
import AjaxLink from '../components/AjaxLink.vue'
import DynamicImg from '../directives/DynamicImg'
import centralTruncate from '../filters/centralTruncate'

export default {
    directives: {
        DynamicImg,
    },
    filters: filters,
    props: ['item', 'isItemExplorer', 'drawerName', 'index', 'removeItem', 'addItem', 'entityName'],
    computed: {
        ...mapState({
            previewIsVisible: (state) => state.documentPreview.isVisible,
        }),
        shortMimeType: function () {
            return centralTruncate(this.document.shortMimeType, 13)
        },
        filename: function () {
            return centralTruncate(this.document.classname, 12)
        },
        name: function () {
            return centralTruncate(this.document.displayable, 12)
        },
        editUrl: function () {
            return this.document.editItem + this.getReferer()
        },
        document: function () {
            return this.item.document ? this.item.document : this.item
        },
        hotspot: function () {
            return this.item.hotspot ? this.item.hotspot : null
        },
        imageCropAlignment: function () {
            return this.item.imageCropAlignment ? this.item.imageCropAlignment : null
        },
    },
    methods: {
        ...mapActions(['documentPreviewInit', 'documentPreviewOpen', 'documentPreviewDestroy']),
        onAddItemButtonClick() {
            // If document is in the explorer panel
            if (this.isItemExplorer) {
                this.addItem(this.item)
            }
        },
        onRemoveItemButtonClick() {
            // Call parent function to remove the document from widget
            this.removeItem(this.item)
        },
        getReferer() {
            return '?referer=' + window.location.pathname
        },
        onPreviewClick() {
            this.documentPreviewOpen({ document: this.document })
        },
        onEditClick(event) {
            if (!this.document.processable) return

            this.$emit('edit', { document: this.document, index: this.index, currentTarget: event.currentTarget })
        },
        onMouseover() {
            this.documentPreviewInit({ document: this.item.document })
        },
        onMouseleave() {
            if (this.previewIsVisible) return

            this.documentPreviewDestroy({ document: this.item.document })
        },
    },
    components: {
        AjaxLink,
    },
}
</script>
