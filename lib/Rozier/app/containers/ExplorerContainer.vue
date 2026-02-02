<template>
    <transition name="slide-left">
        <div
            class="rz-explorer"
            :class="[
                {
                    'filter-explorer-open': isFilterExplorerOpen,
                    'explorer-open': isOpen,
                },
                entityClass,
            ]"
            v-if="isOpen"
        >
            <div class="rz-explorer__inner">
                <div class="rz-explorer__header">
                    <button
                        v-if="isFilterEnable"
                        type="button"
                        @click.prevent="filterExplorerToggle"
                        class="rz-button"
                        :class="[{
                            'rz-button--pill': filterExplorerSelectedItems,
                        }]"
                    >
                        <span class="rz-button__icon" :class="filterExplorerIcon"></span>
                    </button>

                    <form action="#" method="POST" class="rz-explorer__search" v-on:submit.prevent>
                        <input
                            id="search-input"
                            type="search"
                            class="rz-input"
                            name="searchTerms"
                            v-model="searchTerms"
                            autocomplete="off"
                            @keyup.enter.stop.prevent="manualUpdate"
                            :placeholder="searchPlaceHolder"
                        />
                    </form>
                    <button class="rz-button rz-button--primary" @click.prevent="explorerClose">
                        <span class="rz-button__icon uk-icon-rz-close-explorer"></span>
                    </button>
                </div>

                <div class="spinner light" v-if="isLoading"></div>

                <transition name="fade">
                    <div class="uk-sortable rz-explorer__main" v-if="!isLoading">
                        <draggable v-model="items" :options="{ group: { name: entity, put: false } }">
                            <transition-group tag="ul" class="sortable-inner" :class="listClass">
                                <component
                                    v-bind:is="currentListingView"
                                    v-for="(item, index) in items"
                                    :key="item.id"
                                    :is-item-explorer="true"
                                    :add-item="addItem"
                                    :index="index"
                                    :item="item"
                                >
                                </component>
                            </transition-group>
                        </draggable>
                    </div>
                </transition>

                <transition name="fade">
                    <div
                        v-if="filters && filters.nextPage && filters.nextPage > 1"
                        class="rz-explorer__load-more rz-button rz-button--primary"
                        @click.prevent="explorerLoadMore"
                    >
                        <template v-if="!isLoadingMore">
                            <i class="rz-button__label rz-icon-ri--add-line"></i>
                            <span class="rz-button__label">{{ moreItems ? translations[moreItems] : '' }}</span>
                        </template>
                        <template v-else>
                            <transition name="fade">
                                <div class="rz-spinner"></div>
                            </transition>
                        </template>
                    </div>
                </transition>


                <transition name="fade" v-if="filters && items.length && filters.itemCount">
                    <div class="rz-explorer__pagination" >{{ items.length }} / {{ filters.itemCount }}</div>
                </transition>

                <component :is="widgetView"></component>
            </div>
        </div>
    </transition>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import { debounce } from 'lodash'

// Components
import draggable from 'vuedraggable'

export default {
    data: () => {
        return {
            searchPlaceHolder: '',
            drawerId: '',
            drawerFilters: null,
        }
    },
    computed: {
        ...mapState({
            isLoadingMore: (state) => state.explorer.isLoadingMore,
            isLoading: (state) => state.explorer.isLoading,
            isOpen: (state) => state.explorer.isOpen,
            items: (state) => state.explorer.items,
            filters: (state) => state.explorer.filters,
            moreItems: (state) => state.explorer.trans.moreItems,
            translations: (state) => state.translations,
            entity: (state) => state.explorer.entity,
            isFilterExplorerOpen: (state) => state.filterExplorer.isOpen,
            filterExplorerSelectedItems: (state) => state.filterExplorer.selectedItem,
            currentListingView: (state) => state.explorer.currentListingView,
            widgetView: (state) => state.explorer.widgetView,
            isFilterEnable: (state) => state.explorer.isFilterEnable,
            filterExplorerIcon: (state) => state.explorer.filterExplorerIcon,
            entityClass: (state) => 'entity-' + state.explorer.entity,
        }),
        searchTerms: {
            get() {
                return this.$store.getters.getExplorerSearchTerms
            },
            set: debounce(function (searchTerms) {
                if (this.isOpen) {
                    this.$store.dispatch('explorerUpdateSearch', { searchTerms })
                }
            }, 450),
        },
        listClass() {
            const base = 'rz-explorer__list'
            const list = [base]

            if(this.entity === 'file' || this.entity === 'document') {
                 list.push(`${base}--2-columns`)
            }

            return list
        },
    },
    mounted() {
        document.addEventListener('show-explorer', this.onShowExplorer)
    },
    unmounted() {
        document.removeEventListener('show-explorer', this.onShowExplorer)
    },
    methods: {
        ...mapActions([
            'filterExplorerToggle',
            'explorerClose',
            'explorerUpdateSearch',
            'explorerLoadMore',
            'drawersAddItem',
        ]),
        manualUpdate() {
            this.explorerUpdateSearch({ searchTerms: this.searchTerms })
        },
        addItem(item) {
            // this.drawersAddItem({ item })
            document.dispatchEvent(new CustomEvent('add-drawer-item', {
                detail: {
                    item: item,
                    drawerId: this.drawerId,
                },
            }))
        },
        onShowExplorer(event) {
            if (event.detail?.acceptEntity) {
                this.drawerId = event.detail.id || ''
                this.drawerFilters = event.detail.filters || null

                this.$store.dispatch('explorerOpen', {
                    entity: event.detail.acceptEntity,
                    preFilters: this.drawerFilters,
                })
            }
        },
    },
    components: {
        draggable,
    },
}
</script>
