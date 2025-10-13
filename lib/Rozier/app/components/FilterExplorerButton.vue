<template>
    <button type="button" @click.prevent="onClick" class="widget-explorer-logo">
        <i :class="icon"></i>
        <span class="uk-badge uk-badge-notification" v-if="filterExplorerSelectedItems">1</span>
    </button>
</template>
<script>
export default {
    props: {
        onClick: {
            required: true,
            type: Function,
        },
        filterExplorerSelectedItems: {
            required: false,
            type: Object,
        },
        isFilterExplorerOpen: {
            required: true,
            type: Boolean,
        },
        entity: {
            required: true,
        },
        icon: {
            required: true,
            type: String,
        },
    },
}
</script>
