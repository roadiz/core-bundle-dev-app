<template>
    <li class="rz-card" @click.prevent="onAddItemButtonClick">
        <input type="hidden" :name="inputName" :value="item.id" v-if="inputName && item && item.id" />
        <div class="rz-card__overtitle" v-if="parentName || subParentName">
            {{ subParentName ? subParentName : parentName }}
        </div>
        <div v-if="name" class="rz-card__title">{{ name }}</div>
        <div v-if="thumbnailUrl" class="rz-card__asset">
            <picture class="rz-card__img">
                <source v-if="isThumbnailProcessable && !thumbnailUrl.endsWith('.webp')" :srcset="thumbnailUrl + '.webp'" type="image/webp" />
                <img :src="thumbnailUrl" :alt="name || 'Thumbnail'" width="110" height="94">
            </picture>
        </div>

        <div v-if="!isItemExplorer" class="rz-button-group rz-button-group--sm rz-button-group--gap-sm rz-card__action">
            <ajax-link :href="editItemUrl" class="rz-button rz-button--primary rz-button-group__button" v-if="editItemUrl">
                <span class="rz-button__icon rz-icon-ri--equalizer-3-line"></span>
            </ajax-link>
            <button
                type="button"
                class="rz-button rz-button--error-light rz-button-group__button"
                @click.prevent="onRemoveItemButtonClick()"
            >
                <span class="rz-button__icon rz-icon-ri--delete-bin-7-line"></span>
            </button>
        </div>
        <button v-else class="rz-card__action rz-card__action--top rz-button rz-button--primary rz-button-group__button">
            <span class="rz-button__icon rz-icon-ri--add-large-line"></span>
        </button>
    </li>
</template>
<script>
import AjaxLink from './AjaxLink.vue'

export default {
    props: {
        item: {
            type: Object,
        },
        editItem: {
            type: String,
        },
        isItemExplorer: {
            type: Boolean,
        },
        drawerName: {
            type: String,
        },
        index: {
            type: Number,
        },
        removeItem: {
            type: Function,
        },
        addItem: {
            type: Function,
        },
        parentName: {
            type: String,
        },
        subParentName: {
            type: String,
        },
        name: {
            type: String,
        },
        entityName: {
            type: String,
        },
    },
    computed: {
        published: function () {
            return this.item.published
        },
        color: function () {
            if (this.item.nodeType && this.item.nodeType.color) {
                return this.item.nodeType.color
            } else if (this.item.color) {
                return this.item.color
            }
            return null
        },
        inputName() {
            if (this.drawerName) {
                return this.drawerName + '[' + this.index + ']'
            }
        },
        editItemUrl() {
            if (this.editItem) {
                return this.editItem + this.referer
            } else if (this.item.editItem) {
                return this.item.editItem + this.referer
            }

            return null
        },
        referer: function () {
            return '?referer=' + window.location.pathname
        },
        thumbnailUrl: function () {
            if (this.item.thumbnail && this.item.thumbnail.url) {
                return this.item.thumbnail.url
            } else if (this.item.thumbnail && this.item.thumbnail.publicUrl) {
                return this.item.thumbnail.publicUrl
            } else if (typeof this.item.thumbnail === 'string') {
                return this.item.thumbnail
            }
            return null
        },
        isThumbnailProcessable: function () {
            if (this.item.thumbnail && this.item.thumbnail.processable) {
                return this.item.thumbnail.processable
            }
            return false
        },
    },
    methods: {
        onAddItemButtonClick: function () {
            // If document is in the explorer panel
            if (this.isItemExplorer) {
                this.addItem(this.item)
            }
        },
        onRemoveItemButtonClick: function () {
            // Call parent function to remove the document from widget
            this.removeItem(this.item)
        },
    },
    components: {
        AjaxLink,
    },
}
</script>
