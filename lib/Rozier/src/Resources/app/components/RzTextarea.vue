<template>
    <textarea :id="id" :cols="cols" :name="name" :rows="rows">{{ initialValue }}</textarea>
</template>
<script>
export default {
    props: {
        name: {
            required: true,
            type: String,
        },
        id: {
            required: true,
            type: String,
        },
        initialValue: {
            type: String,
        },
        cols: {
            type: Number,
        },
        rows: {
            type: Number,
        },
    },
}
</script>
