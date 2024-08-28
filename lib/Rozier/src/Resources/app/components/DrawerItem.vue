<template>
    <transition name="fade">
        <li
            class="uk-sortable-list-item drawer-item type-label"
            v-if="item"
            @click.prevent="onAddItemButtonClick"
            :class="{ 'has-thumbnail': !!thumbnailUrl, 'not-published': published === false  }">

            <div class="uk-sortable-handle"></div>
            <div class="border" :style="{ backgroundColor: color }"></div>
            <figure class="thumbnail"
                    v-if="thumbnailUrl && !isThumbnailProcessable"
                    :style="{ 'background-image': 'url(' + thumbnailUrl + ')' }"></figure>
            <figure class="thumbnail"
                    v-else-if="thumbnailUrl && isThumbnailProcessable">
                <picture>
                    <source :srcset="thumbnailUrl + '.webp'" type="image/webp" />
                    <img :src="thumbnailUrl" :alt="name">
                </picture>
            </figure>
            <div class="names">
                <p class="parent-name" v-if="parentName">
                    <template v-if="subParentName">
                    <span class="sub">
                        {{ subParentName }}
                    </span>
                    </template>
                    <span class="direct">{{ parentName }}</span>
                </p>
                <span class="name">{{ name }}</span>
                <input type="hidden" :name="inputName" :value="item.id" v-if="inputName && item && item.id" />
                <div class="links" :class="editItemUrl ? '' : 'no-edit'">
                    <ajax-link :href="editItemUrl" class="uk-button link uk-button-mini" v-if="editItemUrl">
                        <i class="uk-icon-rz-pencil"></i>
                    </ajax-link><a href="#"
                                   class="uk-button uk-button-mini link uk-button-danger rz-no-ajax-link"
                                   @click.prevent="onRemoveItemButtonClick()">
                    <i class="uk-icon-rz-trash-o"></i>
                </a>
                </div>
                <a href="#" class="uk-button uk-button-mini link-button">
                    <div class="link-button-inner">
                        <i class="uk-icon-rz-plus"></i>
                    </div>
                </a>
            </div>
        </li>
    </transition>
</template>
<script>
    import AjaxLink from './AjaxLink.vue'

    export default {
        props: {
            item: {
                type: Object
            },
            editItem: {
                type: String
            },
            isItemExplorer: {
                type: Boolean
            },
            drawerName: {
                type: String
            },
            index: {
                type: Number
            },
            removeItem: {
                type: Function
            },
            addItem: {
                type: Function
            },
            parentName: {
                type: String
            },
            subParentName: {
                type: String
            },
            name: {
                type: String
            }
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
            inputName () {
                if (this.drawerName) {
                    return this.drawerName + '[' + this.index + ']'
                }
            },
            editItemUrl () {
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
                } else if (this.item.thumbnail) {
                    return this.item.thumbnail
                }
                return null
            },
            isThumbnailProcessable: function () {
                if (this.item.thumbnail && this.item.thumbnail.processable) {
                    return this.item.thumbnail.processable
                }
                return false
            }
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
            }
        },
        components: {
            AjaxLink
        }
    }
</script>
