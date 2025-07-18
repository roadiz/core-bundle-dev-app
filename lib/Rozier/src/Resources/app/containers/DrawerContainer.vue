<script>
// Inline template 'views/widgets/drawer.html.twig'

import Vue from 'vue'
import { mapActions, mapState } from 'vuex'
import { DRAWERS_UPDATE_LIST } from '../types/mutationTypes'

// Components
import RzButton from '../components/RzButton.vue'
import draggable from 'vuedraggable'
import Dropzone from '../components/Dropzone.vue'
import { NODE_SOURCE_DOCUMENT_ENTITY } from '../types/entityTypes'

export default {
    props: ['entity'],
    data: () => {
        return {
            drawerName: null,
            dropzoneLanguage: window.RozierConfig.messages.dropzone,
            groupName: null,
            isSortable: null,
            entityName: null,
            documentEditDialog: null,
        }
    },
    mounted() {
        // Add the instance to the drawer store
        this.drawersAddInstance(this)

        // Import
        Vue.nextTick(() => {
            this.drawerName = this.$refs.drawer.getAttribute('name')
            this.entityName = this.$refs.drawer.getAttribute('data-entity-name')

            let ids = this.ids
            let entity = this.entity

            // Get initial needed data
            if (!ids) {
                ids = JSON.parse(this.$refs.drawer.getAttribute('data-initial-items'))
            }

            if (!entity) {
                entity = this.$refs.drawer.getAttribute('data-accept-entity')
            }

            const isSortable = this.$refs.drawer.getAttribute('data-is-sortable')
            const minLengthEl = this.$refs.drawer.getAttribute('data-min-length')
            const maxLengthEl = this.$refs.drawer.getAttribute('data-max-length')
            const maxLength = maxLengthEl ? parseInt(maxLengthEl, 10) : 9999
            const minLength = minLengthEl ? parseInt(minLengthEl, 10) : 0

            // Change draggable config
            this.groupName = entity
            this.isSortable = isSortable === 'true'

            // Get specific filter
            const nodeTypes = this.$refs.drawer.getAttribute('data-nodetypes')
            const nodeTypeField = this.$refs.drawer.getAttribute('data-nodetypefield')
            const nodeTypeName = this.$refs.drawer.getAttribute('data-nodetypename')
            const providerClass = this.$refs.drawer.getAttribute('data-provider-class')
            const locale = this.$refs.drawer.getAttribute('data-locale')
            const providerOptions = JSON.parse(
                decodeURIComponent(this.$refs.drawer.getAttribute('data-provider-options'))
            )

            // Merge specific filter in one object
            const filters = { nodeTypes, nodeTypeField, providerClass, providerOptions, nodeTypeName, _locale: locale }

            // Init data
            this.drawersInitData({
                drawer: this.drawer,
                entity,
                ids,
                filters,
                maxLength,
                minLength,
                entityName: this.entityName,
            })
        })
    },
    beforeDestroy: function () {
        this.drawersRemoveInstance(this)
    },
    computed: {
        ...mapState({
            isActive: function () {
                let drawer = this.$store.getters.drawersGetById(this._uid)
                return drawer ? drawer.isActive : false
            },
            trans: (state) => state.drawers.trans,
            currentListingView: (state) => state.explorer.currentListingView,
        }),
        drawer() {
            return this.$store.getters.drawersGetById(this._uid)
        },
        trans: function () {
            return this.drawer.trans
        },
        items: {
            get() {
                return this.drawer.items
            },
            set(newList) {
                this.$store.commit(DRAWERS_UPDATE_LIST, {
                    drawer: this.drawer,
                    newList,
                })
            },
        },
    },
    methods: {
        ...mapActions([
            'drawersAddInstance',
            'drawersRemoveInstance',
            'drawersRemoveItem',
            'drawersAddItem',
            'drawersMoveItem',
            'drawersInitData',
            'drawersExplorerButtonClick',
            'drawersDropzoneButtonClick',
        ]),
        getOptions() {
            return {
                group: {
                    name: this.groupName,
                    put: this.drawer.acceptMore,
                },
                sort: this.isSortable,
            }
        },
        onExplorerButtonClick: function () {
            this.drawersExplorerButtonClick(this.drawer)
        },
        onDropzoneButtonClick: function () {
            this.drawersDropzoneButtonClick(this.drawer)
        },
        removeItem: function (item) {
            this.drawersRemoveItem({
                drawer: this.drawer,
                item,
            })
        },
        addItem: function (item, newIndex = null) {
            this.drawersAddItem({
                drawer: this.drawer,
                item,
                newIndex,
            })
        },
        showSuccess: function (file, response) {
            if (this.entityName === NODE_SOURCE_DOCUMENT_ENTITY) {
                this.addItem({
                    document: response.document,
                    hotspot: null,
                    imageCropAlignment: null,
                })
            } else {
                this.addItem(response.document)
            }
        },
        showError: function (file, error, xhr) {
            console.error(file)
            console.error(error)
            console.error(xhr)
        },
        onEditItem(event) {
            const dialog = document.createElement('document-edit-dialog')

            dialog.setAttribute('template-path', this.$el.getAttribute('data-document-alignment-template-path'))
            dialog.setAttribute('title', event.document.classname)
            dialog.setAttribute('edit-url', event.document.editItem + '?referer=' + window.location.pathname)
            dialog.setAttribute('image-path', event.document.editImageUrl)
            dialog.setAttribute('input-base-name', `${this.drawerName}[${event.index}]`)
            dialog.setAttribute('origin-button-id', event.currentTarget.id)

            document.body.appendChild(dialog)

            dialog.showModal()
        },
    },
    components: {
        RzButton,
        draggable,
        Dropzone,
    },
}
</script>
