import Vue from 'vue'
import store from './store'

// Services
import KeyboardEventService from './services/KeyboardEventService'
import LoginCheckService from './services/LoginCheckService'

// Containers
import NodesSearchContainer from './containers/NodesSearchContainer.vue'
import DrawerContainer from './containers/DrawerContainer.vue'
import ExplorerContainer from './containers/ExplorerContainer.vue'
import FilterExplorerContainer from './containers/FilterExplorerContainer.vue'
import TagsEditorContainer from './containers/TagsEditorContainer.vue'
import DocumentPreviewContainer from './containers/DocumentPreviewContainer.vue'
import BlanchetteEditorContainer from './containers/BlanchetteEditorContainer.vue'
import ModalContainer from './containers/ModalContainer.vue'

// Components
import Overlay from './components/Overlay.vue'

import { KEYBOARD_EVENT_ESCAPE } from './types/mutationTypes'
import customElements from './custom-elements'

/**
 * Root entry for VueJS App.
 */
export default class AppVue {
    constructor() {
        this.services = []
        this.navTrees = null
        this.containers = null
        this.documentExplorer = null
        this.mainContentComponents = []
        this.registeredContainers = {
            NodesSearchContainer,
            DrawerContainer,
            ExplorerContainer,
            FilterExplorerContainer,
            TagsEditorContainer,
            DocumentPreviewContainer,
            BlanchetteEditorContainer,
            ModalContainer,
        }

        this.registeredComponents = {
            Overlay,
        }

        this.vuejsElements = {
            ...this.registeredComponents,
            ...this.registeredContainers,
        }

        this.init()
        this.initListeners()
    }

    init() {
        this.buildNavTrees()
        this.buildOtherContainers()
        this.buildMainContentComponents()
        this.initServices()
    }

    initListeners() {
        window.addEventListener('pagechange', this.onPageChange.bind(this))
        window.addEventListener('pageload', this.onPageLoaded.bind(this))
    }

    initServices() {
        this.services.push(new KeyboardEventService(store))
        this.services.push(new LoginCheckService(store))
    }

    onPageChange() {
        store.commit(KEYBOARD_EVENT_ESCAPE)
    }

    onPageLoaded(e) {
        this.buildMainContentComponents(e.detail)
    }

    destroyMainContentComponents() {
        this.mainContentComponents.forEach((component) => {
            component.$destroy()
        })
    }

    buildDocumentExplorer() {
        if (document.getElementById('document-explorer')) {
            this.documentExplorer = this.buildComponent('#document-explorer')
        }
    }

    buildOtherContainers() {
        if (document.getElementById('vue-containers')) {
            this.containers = this.buildComponent('#vue-containers')
        }
    }

    buildNavTrees() {
        if (document.getElementById('main-trees')) {
            this.navTrees = this.buildComponent('#main-trees')
        }
    }

    buildMainContentComponents() {
        // Destroy old components
        this.destroyMainContentComponents()

        // Looking for new vuejs component
        const vueComponents = document.querySelectorAll(
            '#main-content [data-vuejs]',
        )

        // Create each component
        vueComponents.forEach((el) => {
            this.mainContentComponents.push(this.buildComponent(el))
        })
    }

    buildComponent(el) {
        return new Vue({
            el: el,
            delimiters: ['${', '}'],
            store,
            components: this.vuejsElements,
            ignoredElements: Object.keys(customElements),
        })
    }
}
