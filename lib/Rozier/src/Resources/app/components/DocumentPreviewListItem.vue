<template>
    <transition name="fade">
        <li v-if="document"
            :title="document.classname"
            class="image-document uk-sortable-list-item documents-widget-sortable-list-item"
            data-uk-tooltip="{animation:true, pos:'bottom'}"
            @mouseleave="onMouseleave"
            @mouseover="onMouseover">

            <div class="preview-zoom" @click.prevent="onPreviewClick">
                <i class="uk-icon-search-plus"></i>
            </div>

            <div @click.prevent="onAddItemButtonClick">

                <div class="uk-sortable-handle"></div>
                <div class="document-border"></div>

                <div class="document-overflow">
                    <template v-if="document.isPrivate">
                        <div class="document-platform-icon"><i class="uk-icon-lock"></i></div>
                    </template>
                    <template v-else>
                        <template v-if="document.isSvg">
                            <div v-html="document.previewHtml" class="svg"></div>
                        </template>
                        <template v-else-if="document.isImage && !document.isWebp">
                            <picture>
                                <source :srcset="document.thumbnail80 + '.webp'" type="image/webp">
                                <img class="document-image"
                                     width="80"
                                     height="80"
                                     loading="lazy"
                                     :src="document.thumbnail80" />
                            </picture>
                        </template>
                        <template v-else-if="document.isImage">
                            <img class="document-image" width="80" height="80"
                                 src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                                 v-dynamic-img="document.thumbnail80" />
                        </template>
                        <template v-else>
                            <img v-if="document.hasThumbnail"
                                 class="document-image"
                                 width="80" height="80"
                                 src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                                 v-dynamic-img="document.thumbnail80" />
                            <div class="document-platform-icon"><i :class="'uk-icon-file-' + document.icon +'-o'"></i></div>
                        </template>
                    </template>

                    <template v-if="drawerName && entityName">
                        <input type="hidden" :name="drawerName + '[' + index +'][document]'" :value="document.id" />
                        <input v-if="hotspot" type="hidden" :name="drawerName + '[' + index +'][hotspot]'" :value="JSON.stringify(hotspot)" />
                        <input v-if="imageCropAlignment" type="hidden" :name="drawerName + '[' + index +'][imageCropAlignment]'" :value="imageCropAlignment" />
                    </template>
                    <template v-else-if="drawerName">
                        <input type="hidden" :name="drawerName + '[' + index +']'" :value="document.id" />
                    </template>

                    <div class="document-links">
                        <ajax-link
                            :href="editUrl"
                            class="uk-button document-link uk-button-mini">
                            <i class="uk-icon-rz-pencil"></i>
                        </ajax-link><a
                            href="#"
                            @click.prevent="onRemoveItemButtonClick()"
                            class="uk-button uk-button-mini document-link uk-button-danger rz-no-ajax-link">
                            <i class="uk-icon-rz-trash-o"></i>
                        </a>
                    </div>
                    <template v-if="document.embedPlatform">
                        <div class="document-mime-type">{{ document.embedPlatform }}</div>
                        <div class="document-platform-icon"><i :class="'uk-icon-' + document.icon"></i></div>
                    </template>
                    <template v-else>
                        <div class="document-mime-type">{{ shortMimeType }}</div>
                    </template>

                    <a data-document-widget-link-document href="#" class="uk-button uk-button-mini link-button">
                        <div class="link-button-inner">
                            <i class="uk-icon-rz-plus"></i>
                        </div>
                    </a>
                </div>
                <div class="document-name">{{ filename }}</div>
            </div>
        </li>
    </transition>
</template>

<script>
import {mapActions} from 'vuex'
import filters from '../filters'
import AjaxLink from '../components/AjaxLink.vue'
import DynamicImg from '../directives/DynamicImg'
import centralTruncate from '../filters/centralTruncate'

export default {
        props: ['item', 'isItemExplorer', 'drawerName', 'index', 'removeItem', 'addItem', 'entityName'],
        directives: {
            DynamicImg
        },
        filters: filters,
        computed: {
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
                return (this.item.document) ? this.item.document : this.item
            },
            hotspot: function () {
                return (this.item.hotspot) ? this.item.hotspot : null
            },
            imageCropAlignment: function () {
                return (this.item.imageCropAlignment) ? this.item.imageCropAlignment : null
            }
        },
        methods: {
            ...mapActions([
                'documentPreviewInit',
                'documentPreviewOpen',
                'documentPreviewDestroy'
            ]),
            onAddItemButtonClick () {
                // If document is in the explorer panel
                if (this.isItemExplorer) {
                    this.addItem(this.item)
                }
            },
            onRemoveItemButtonClick () {
                // Call parent function to remove the document from widget
                this.removeItem(this.item)
            },
            getReferer () {
                return '?referer=' + window.location.pathname
            },
            onPreviewClick () {
                this.documentPreviewOpen()
            },
            onMouseover () {
                this.documentPreviewInit({ document: this.item })
            },
            onMouseleave () {
                this.documentPreviewDestroy({ document: this.item })
            }
        },
        components: {
            AjaxLink
        }
    }
</script>
