<template lang="html">
    <div>
        <warning-modal
            v-if="!connected"
            :title="translations.sessionExpireTitle"
            :content="translations.sessionExpireContent"
            :link-label="translations.login"
            :closeable="true"
            :link-url="linkUrl">
        </warning-modal>
        <warning-modal
            v-else-if="!healthChecked"
            :title="translations.healthCheckedFailedTitle"
            :closeable="false"
            :content="translations.healthCheckedFailedContent">
        </warning-modal>
    </div>
</template>

<script>
    import Vue from 'vue'
    import { mapState } from 'vuex'

    // Components
    import vmodal from 'vue-js-modal'
    import WarningModal from '../components/WarningModal.vue'

    Vue.use(vmodal)

    export default {
        data () {
            return {
                linkUrl: window.RozierRoot.routes.loginPage + '?_home=1'
            }
        },
        computed: {
            ...mapState({
                connected: state => state.connected,
                healthChecked: state => state.healthChecked,
                translations: state => state.translations
            })
        },
        components: {
            WarningModal
        }
    }
</script>
