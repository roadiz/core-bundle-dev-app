<template>
    <button type="button" class="uk-button uk-button-small" :class="{ 'uk-active': isActive }" @click.prevent="callback">
        <slot></slot>
    </button>
</template>

<script>
export default {
    name: 'rz-button',
    props: ['isActive', 'callback'],
}
</script>
