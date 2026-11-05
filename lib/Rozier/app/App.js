import Vue from 'vue'
import store from './store'

// Services
import LoginCheckService from './services/LoginCheckService'

import customElements from './custom-elements'

/**
 * Root entry for VueJS App.
 */
export default class AppVue {
    constructor() {
        this.services = []
        this.containers = null
        this.documentExplorer = null
        this.mainContentComponents = []
        this.registeredContainers = {}

        this.registeredComponents = {}

        this.vuejsElements = {
            ...this.registeredComponents,
            ...this.registeredContainers,
        }

        Vue.config.ignoredElements = Object.keys(customElements)

        this.init()
        this.initListeners()
    }

    init() {
        this.buildOtherContainers()
        this.buildMainContentComponents()
        this.initServices()
    }

    initListeners() {
        window.addEventListener('pageload', this.onPageLoaded.bind(this))
    }

    initServices() {
        this.services.push(new LoginCheckService())
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

    buildMainContentComponents() {
        // Destroy old components
        this.destroyMainContentComponents()

        // Looking for new vuejs component
        const vueComponents = document.querySelectorAll('#rz-page [data-vuejs]')

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
        })
    }
}
