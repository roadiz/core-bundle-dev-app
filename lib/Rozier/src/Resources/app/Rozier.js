import $ from 'jquery'
import Lazyload from './Lazyload'
import EntriesPanel from './components/panels/EntriesPanel'
import VueApp from './App'
import { PointerEventsPolyfill } from './utils/plugins'
import { TweenLite, Expo } from 'gsap'
import NodeTreeContextActions from './components/trees/NodeTreeContextActions'
import RozierMobile from './RozierMobile'
import bulkActions from './widgets/GenericBulkActions'

require('gsap/ScrollToPlugin')
/**
 * Rozier root entry
 */
export default class Rozier {
    constructor() {
        this.$window = null
        this.$body = null

        this.windowWidth = null
        this.windowHeight = null
        this.resizeFirst = true
        this.mobile = null
        this.ajaxToken = null

        this.nodeTrees = []
        this.treeTrees = []

        this.$userPanelContainer = null
        this.$minifyTreePanelButton = null
        this.$mainTrees = null
        this.$mainTreesContainer = null
        this.$mainTreeElementName = null
        this.$treeContextualButton = null
        this.$nodesSourcesSearch = null
        this.nodesSourcesSearchHeight = null
        this.$nodeTreeHead = null
        this.nodeTreeHeadHeight = null
        this.$treeScrollCont = null
        this.$treeScroll = null
        this.treeScrollHeight = null

        this.$mainContentScrollable = null
        this.mainContentScrollableWidth = null
        this.mainContentScrollableOffsetLeft = null
        this.$backTopBtn = null
        this.entriesPanel = null
        this.collapsedNestableState = null

        this.maintreeElementNameRightClick = this.maintreeElementNameRightClick.bind(this)
        this.onNestableNodeTreeChange = this.onNestableNodeTreeChange.bind(this)
        this.onNestableTagTreeChange = this.onNestableTagTreeChange.bind(this)
        this.onNestableFolderTreeChange = this.onNestableFolderTreeChange.bind(this)
        this.backTopBtnClick = this.backTopBtnClick.bind(this)
        this.resize = this.resize.bind(this)
        this.onNestableCollapse = this.onNestableCollapse.bind(this)
        this.onNestableExpand = this.onNestableExpand.bind(this)
    }

    onDocumentReady() {
        /*
         * Store Rozier configuration
         */
        for (let index in window.temp) {
            window.Rozier[index] = window.temp[index]
        }

        /*
         * override default nestable settings in order to
         * store toggle state between reloads.
         */
        this.setupCollapsedNestableState()

        this.lazyload = new Lazyload()
        this.entriesPanel = new EntriesPanel()
        this.vueApp = new VueApp()

        this.$window = $(window)
        this.$body = $('body')

        // --- Selectors --- //
        this.$userPanelContainer = $('#user-panel-container')
        this.$minifyTreePanelButton = $('#minify-tree-panel-button')
        this.$mainTrees = $('#main-trees')
        this.$mainTreesContainer = $('#main-trees-container')
        this.$nodesSourcesSearch = $('#nodes-sources-search')
        this.$mainContentScrollable = $('#main-content-scrollable')
        this.$backTopBtn = $('#back-top-button')

        // Pointer events polyfill
        if (!window.Modernizr.testProp('pointerEvents')) {
            PointerEventsPolyfill.initialize({ selector: '#main-trees-overlay' })
        }

        // Minify trees panel toggle button
        this.$minifyTreePanelButton.on('click', this.toggleTreesPanel)

        // this.$body.on('markdownPreviewOpen', '.markdown-editor-preview', this.toggleTreesPanel);
        document.body.addEventListener('markdownPreviewOpen', this.openTreesPanel, false)

        // Back top btn
        this.$backTopBtn.on('click', this.backTopBtnClick)

        this.$window.on('resize', this.resize)
        this.$window.trigger('resize')

        this.lazyload.generalBind()
        this.bindMainNodeTreeLangs()

        /*
         * Fetch main tree widgets for the first time
         */
        this.refreshMainNodeTree()
        this.refreshMainTagTree()
        this.refreshMainFolderTree()

        /*
         * init generic bulk actions widget
         */
        bulkActions()
        window.addEventListener('pageshowend', () => {
            bulkActions()
            this.syncCollapsedNestableState()
        })
    }

    setupCollapsedNestableState() {
        if (window.localStorage) {
            this.collapsedNestableState = window.localStorage.getItem('collapsed.uk.nestable')
            /*
             * First login into backoffice
             */
            if (!this.collapsedNestableState) {
                this.saveCollapsedNestableState(null)
                this.collapsedNestableState = window.localStorage.getItem('collapsed.uk.nestable')
            }
            this.collapsedNestableState = JSON.parse(this.collapsedNestableState)

            window.UIkit.on('beforeready.uk.dom', function () {
                $.extend(window.UIkit.components.nestable.prototype, {
                    collapseItem: function (li) {
                        var lists = li.children(this.options._listClass)
                        if (lists.length) {
                            li.addClass(this.options.collapsedClass)
                        }
                        /*
                         * Create new event on collapse
                         */
                        document.dispatchEvent(
                            new CustomEvent('collapse.uk.nestable', {
                                detail: li,
                            })
                        )
                    },
                })
                $.extend(window.UIkit.components.nestable.prototype, {
                    expandItem: function (li) {
                        li.removeClass(this.options.collapsedClass)
                        /*
                         * Create new event on expand
                         */
                        document.dispatchEvent(
                            new CustomEvent('expand.uk.nestable', {
                                detail: li,
                            })
                        )
                    },
                })
            })
        }
    }

    saveCollapsedNestableState(state = null) {
        if (state === null) {
            state = {
                nodes: [],
                tags: [],
                folders: [],
            }
        }
        window.localStorage.setItem('collapsed.uk.nestable', JSON.stringify(state))
    }

    syncCollapsedNestableState() {
        this.collapsedNestableState.nodes.forEach((value) => {
            const li = document.querySelectorAll('.uk-nestable-item[data-node-id="' + $.escapeSelector(value) + '"]')
            li.forEach((element) => {
                element.classList.add('uk-collapsed')
            })
        })
        this.collapsedNestableState.tags.forEach((value) => {
            const li = document.querySelectorAll('.uk-nestable-item[data-tag-id="' + $.escapeSelector(value) + '"]')
            li.forEach((element) => {
                element.classList.add('uk-collapsed')
            })
        })
        this.collapsedNestableState.folders.forEach((value) => {
            const li = document.querySelectorAll('.uk-nestable-item[data-folder-id="' + $.escapeSelector(value) + '"]')
            li.forEach((element) => {
                element.classList.add('uk-collapsed')
            })
        })
    }

    /**
     * init nestable for ajax
     */
    initNestables() {
        this.syncCollapsedNestableState()

        document.querySelectorAll('.uk-nestable').forEach((element) => {
            /*
             * make drag&drop only available on handle
             * very important for Touch based device which need to
             * scroll on trees.
             */
            const options = {
                handleClass: 'uk-nestable-handle',
            }

            if (element.classList.contains('nodetree')) {
                options.group = 'nodeTree'
            } else if (element.classList.contains('tagtree')) {
                options.group = 'tagTree'
            } else if (element.classList.contains('foldertree')) {
                options.group = 'folderTree'
            }

            window.UIkit.nestable(element, options)
        })
        document.removeEventListener('collapse.uk.nestable', this.onNestableCollapse)
        document.addEventListener('collapse.uk.nestable', this.onNestableCollapse)
        document.removeEventListener('expand.uk.nestable', this.onNestableExpand)
        document.addEventListener('expand.uk.nestable', this.onNestableExpand)
    }

    /**
     * Bind main trees
     */
    bindMainTrees() {
        // TREES
        let $nodeTree = $('.nodetree-widget .root-tree')
        if ($nodeTree.length) {
            $nodeTree.off('change.uk.nestable')
            $nodeTree.on('change.uk.nestable', this.onNestableNodeTreeChange)
        }

        let $tagTree = $('.tagtree-widget .root-tree')
        if ($tagTree.length) {
            $tagTree.off('change.uk.nestable')
            $tagTree.on('change.uk.nestable', this.onNestableTagTreeChange)
        }

        let $folderTree = $('.foldertree-widget .root-tree')
        if ($folderTree.length) {
            $folderTree.off('change.uk.nestable')
            $folderTree.on('change.uk.nestable', this.onNestableFolderTreeChange)
        }

        // Tree element name
        this.$mainTreeElementName = this.$mainTrees.find('.tree-element-name')
        if (this.$mainTreeElementName.length) {
            this.$mainTreeElementName.off('contextmenu', this.maintreeElementNameRightClick)
            this.$mainTreeElementName.on('contextmenu', this.maintreeElementNameRightClick)
        }
    }

    /**
     * Main tree element name right click.
     * @return {boolean}
     */
    maintreeElementNameRightClick(e) {
        let $contextualMenu = $(e.currentTarget).parent().find('.tree-contextualmenu')
        if ($contextualMenu.length) {
            if ($contextualMenu[0].className.indexOf('uk-open') === -1) {
                $contextualMenu.addClass('uk-open')
            } else $contextualMenu.removeClass('uk-open')
        }

        return false
    }

    /**
     * Bind main node tree langs.
     *
     * @return {boolean}
     */
    bindMainNodeTreeLangs() {
        $('body').on('click', '#tree-container .nodetree-langs a', (event) => {
            this.lazyload.canvasLoader.show()
            let $link = $(event.currentTarget)
            let translationId = parseInt($link.attr('data-translation-id'))
            this.refreshMainNodeTree(translationId)
            return false
        })
    }

    fetchSessionMessages() {
        return new Promise(async (resolve, reject) => {
            const query = new URLSearchParams({
                _action: 'messages',
                _token: this.ajaxToken,
            })
            const url = this.routes.ajaxSessionMessages + '?' + query.toString()
            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                })
                const data = await response.json()
                if (!data.messages) {
                    reject()
                }
                resolve(data.messages)
            } catch (e) {
                reject()
            }
        })
    }
    /**
     * Get messages.
     */
    async getMessages() {
        const messages = await this.fetchSessionMessages()
        if (typeof messages.confirm !== 'undefined' && messages.confirm.length > 0) {
            for (let i = messages.confirm.length - 1; i >= 0; i--) {
                window.UIkit.notify({
                    message: messages.confirm[i],
                    status: 'success',
                    timeout: 2000,
                    pos: 'top-center',
                })
            }
        }

        if (typeof messages.error !== 'undefined' && messages.error.length > 0) {
            for (let j = data.messages.error.length - 1; j >= 0; j--) {
                window.UIkit.notify({
                    message: data.messages.error[j],
                    status: 'error',
                    timeout: 2000,
                    pos: 'top-center',
                })
            }
        }
    }

    /**
     * @param translationId
     */
    refreshAllNodeTrees(translationId) {
        const promises = []
        promises.push(this.refreshMainNodeTree(translationId))

        /*
         * Stack trees
         */
        if (this.lazyload.stackNodeTrees.treeAvailable()) {
            promises.push(this.lazyload.stackNodeTrees.refreshNodeTree())
        }

        /*
         * Children node fields widgets;
         */
        if (this.lazyload.childrenNodesFields.treeAvailable()) {
            for (let i = this.lazyload.childrenNodesFields.$nodeTrees.length - 1; i >= 0; i--) {
                let $nodeTree = this.lazyload.childrenNodesFields.$nodeTrees.eq(i)
                promises.push(this.lazyload.childrenNodesFields.refreshNodeTree($nodeTree))
            }
        }
        return Promise.all(promises)
    }

    /**
     * Refresh only main nodeTree.
     *
     * @param {Number|null|undefined} translationId
     */
    async refreshMainNodeTree(translationId = undefined) {
        let $currentNodeTree = $('#tree-container').find('.nodetree-widget').eq(0)
        if (!$currentNodeTree.length) {
            console.debug('No main node-tree available.')
            return
        }

        let $currentRootTree = $currentNodeTree.find('.root-tree').eq(0)
        if ($currentRootTree.length && !translationId) {
            translationId = $currentRootTree.attr('data-translation-id')
        }

        try {
            const query = new URLSearchParams({
                _token: this.ajaxToken,
                _action: 'requestMainNodeTree',
                translationId: translationId || null,
            })
            const url = this.routes.nodesTreeAjax + '?' + query.toString()
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
            })
            if (!response.ok) {
                throw response
            }
            const data = await response.json()
            if (typeof data.nodeTree !== 'undefined') {
                await this.fadeOut($currentNodeTree)
                $currentNodeTree.replaceWith(data.nodeTree)
                $currentNodeTree = $('#tree-container').find('.nodetree-widget')

                await this.fadeIn($currentNodeTree)
                this.initNestables()
                this.bindMainTrees()
                this.resize()
                this.lazyload.bindAjaxLink()

                if (this.lazyload.nodeTreeContextActions) {
                    this.lazyload.nodeTreeContextActions.unbind()
                }

                this.lazyload.nodeTreeContextActions = new NodeTreeContextActions()
            }
        } catch (e) {
            console.log('[Rozier.refreshMainNodeTree] Retrying in 3 seconds')
            // Wait for background jobs to be done
            window.setTimeout(() => {
                this.refreshMainNodeTree(translationId)
            }, 3000)
        }

        this.lazyload.canvasLoader.hide()
    }

    /**
     * Refresh only main tagTree.
     *
     */
    async refreshMainTagTree() {
        let $currentTagTree = $('#tree-container').find('.tagtree-widget')

        if (!$currentTagTree.length) {
            console.debug('No main tag-tree available.')
            return
        }

        const query = new URLSearchParams({
            _token: this.ajaxToken,
            _action: 'requestMainTagTree',
        })
        const url = this.routes.tagsTreeAjax + '?' + query.toString()
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        })
        const data = await response.json()
        if (typeof data.tagTree !== 'undefined') {
            await this.fadeOut($currentTagTree)
            $currentTagTree.replaceWith(data.tagTree)
            $currentTagTree = $('#tree-container').find('.tagtree-widget')
            await this.fadeIn($currentTagTree)
            this.initNestables()
            this.bindMainTrees()
            this.resize()
            this.lazyload.bindAjaxLink()
        }
        this.lazyload.canvasLoader.hide()
    }

    /**
     * Refresh only main folderTree.
     */
    async refreshMainFolderTree() {
        let $currentFolderTree = $('#tree-container').find('.foldertree-widget')

        if (!$currentFolderTree.length) {
            console.debug('No main folder-tree available.')
            return
        }

        const query = new URLSearchParams({
            _token: this.ajaxToken,
            _action: 'requestMainFolderTree',
        })
        const url = this.routes.foldersTreeAjax + '?' + query.toString()
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        })
        const data = await response.json()
        if (typeof data.folderTree !== 'undefined') {
            await this.fadeOut($currentFolderTree)
            $currentFolderTree.replaceWith(data.folderTree)
            $currentFolderTree = $('#tree-container').find('.foldertree-widget')
            await this.fadeIn($currentFolderTree)
            this.initNestables()
            this.bindMainTrees()
            this.resize()
            this.lazyload.bindAjaxLink()
        }

        this.lazyload.canvasLoader.hide()
    }

    /**
     * @param {jQuery} element
     * @return {Promise<unknown>}
     */
    async fadeIn(element) {
        return new Promise((resolve, reject) => {
            element.fadeIn(() => {
                resolve()
            })
        })
    }

    /**
     * @param {jQuery} element
     * @return {Promise<unknown>}
     */
    async fadeOut(element) {
        return new Promise((resolve, reject) => {
            element.fadeOut('slow', () => {
                resolve()
            })
        })
    }

    /**
     * Toggle trees panel
     */
    toggleTreesPanel() {
        $('#main-container-inner').toggleClass('trees-panel--minified')
        $('#main-content').toggleClass('maximized')
        $('#minify-tree-panel-button').find('i').toggleClass('uk-icon-rz-panel-tree-open')
        $('#minify-tree-panel-area').toggleClass('tree-panel-hidden')

        return false
    }

    openTreesPanel() {
        if ($('#main-container-inner').hasClass('trees-panel--minified')) {
            this.toggleTreesPanel(null)
        }

        return false
    }

    /**
     * Toggle user panel
     */
    toggleUserPanel() {
        $('#user-panel').toggleClass('minified')
        return false
    }

    onNestableCollapse({ detail }) {
        if (detail[0]) {
            switch (true) {
                case detail[0].getAttribute('data-node-id') !== null:
                    this.collapsedNestableState.nodes.push(detail[0].getAttribute('data-node-id'))
                    break
                case detail[0].getAttribute('data-tag-id') !== null:
                    this.collapsedNestableState.tags.push(detail[0].getAttribute('data-tag-id'))
                    break
                case detail[0].getAttribute('data-folder-id') !== null:
                    this.collapsedNestableState.folders.push(detail[0].getAttribute('data-folder-id'))
                    break
            }

            this.saveCollapsedNestableState(this.collapsedNestableState)
        }
    }

    onNestableExpand({ detail }) {
        if (detail[0]) {
            switch (true) {
                case detail[0].getAttribute('data-node-id') !== null:
                    this.collapsedNestableState.nodes.splice(
                        this.collapsedNestableState.nodes.indexOf(detail[0].getAttribute('data-node-id')),
                        1
                    )
                    break
                case detail[0].getAttribute('data-tag-id') !== null:
                    this.collapsedNestableState.tags.splice(
                        this.collapsedNestableState.tags.indexOf(detail[0].getAttribute('data-tag-id')),
                        1
                    )
                    break
                case detail[0].getAttribute('data-folder-id') !== null:
                    this.collapsedNestableState.folders.splice(
                        this.collapsedNestableState.folders.indexOf(detail[0].getAttribute('data-folder-id')),
                        1
                    )
                    break
            }

            this.saveCollapsedNestableState(this.collapsedNestableState)
        }
    }

    /**
     * @param event
     * @param {HTMLElement} rootEl
     * @param {HTMLElement} el
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableNodeTreeChange(event, rootEl, el, status) {
        let element = $(el)
        /*
         * If node removed, do not do anything, the other change.uk.nestable nodeTree will be triggered
         */
        if (status === 'removed') {
            return false
        }
        let nodeId = parseInt(element.attr('data-node-id'))
        let parentNodeId = null
        if (element.parents('.nodetree-element').length) {
            parentNodeId = parseInt(element.parents('.nodetree-element').eq(0).attr('data-node-id'))
        } else if (element.parents('.stack-tree-widget').length) {
            parentNodeId = parseInt(element.parents('.stack-tree-widget').eq(0).attr('data-parent-node-id'))
        } else if (element.parents('.children-node-widget').length) {
            parentNodeId = parseInt(element.parents('.children-node-widget').eq(0).attr('data-parent-node-id'))
        }

        /*
         * When dropping to route
         * set parentNodeId to NULL
         */
        if (isNaN(parentNodeId)) {
            parentNodeId = null
        }

        /*
         * User dragged node inside itself
         * It will destroy the Internet !
         */
        if (nodeId === parentNodeId) {
            console.error('You cannot move a node inside itself!')
            window.location.reload()
            return false
        }

        const postData = {
            _token: this.ajaxToken,
            _action: 'updatePosition',
            nodeId: nodeId,
            newParent: parentNodeId,
        }

        /*
         * Get node siblings id to compute new position
         */
        if (element.next().length && typeof element.next().attr('data-node-id') !== 'undefined') {
            postData.nextNodeId = parseInt(element.next().attr('data-node-id'))
        } else if (element.prev().length && typeof element.prev().attr('data-node-id') !== 'undefined') {
            postData.prevNodeId = parseInt(element.prev().attr('data-node-id'))
        }

        try {
            const response = await fetch(this.routes.nodeAjaxEdit.replace('%nodeId%', nodeId), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new URLSearchParams(postData),
            })
            if (!response.ok) {
                throw response
            }
            const data = await response.json()
            window.UIkit.notify({
                message: data.responseText || data.detail,
                status: data.status,
                timeout: 3000,
                pos: 'top-center',
            })
        } catch (response) {
            const data = await response.json()
            window.UIkit.notify({
                message: data.error_message || data.detail,
                status: 'danger',
                timeout: 3000,
                pos: 'top-center',
            })
        }
    }

    /**
     * @param event
     * @param {HTMLElement} rootEl
     * @param {HTMLElement} el
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableTagTreeChange(event, rootEl, el, status) {
        let element = $(el)

        /*
         * If tag removed, do not do anything, the other tagTree will be triggered
         */
        if (status === 'removed') {
            return false
        }

        let tagId = parseInt(element.attr('data-tag-id'))
        let parentTagId = null
        if (element.parents('.tagtree-element').length) {
            parentTagId = parseInt(element.parents('.tagtree-element').eq(0).attr('data-tag-id'))
        } else if (element.parents('.root-tree').length) {
            parentTagId = parseInt(element.parents('.root-tree').eq(0).attr('data-parent-tag-id'))
        }
        /*
         * When dropping to route
         * set parentTagId to NULL
         */
        if (isNaN(parentTagId)) {
            parentTagId = null
        }

        /*
         * User dragged tag inside itself
         * It will destroy the Internet !
         */
        if (tagId === parentTagId) {
            console.error('You cannot move a tag inside itself!')
            alert('You cannot move a tag inside itself!')
            window.location.reload()
            return false
        }

        let postData = {
            _token: this.ajaxToken,
            _action: 'updatePosition',
            tagId: tagId,
            newParent: parentTagId,
        }

        /*
         * Get tag siblings id to compute new position
         */
        if (element.next().length && typeof element.next().attr('data-tag-id') !== 'undefined') {
            postData.nextTagId = parseInt(element.next().attr('data-tag-id'))
        } else if (element.prev().length && typeof element.prev().attr('data-tag-id') !== 'undefined') {
            postData.prevTagId = parseInt(element.prev().attr('data-tag-id'))
        }

        try {
            const response = await fetch(this.routes.tagAjaxEdit.replace('%tagId%', tagId), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new URLSearchParams(postData),
            })
            if (!response.ok) {
                throw response
            }
            const data = await response.json()
            window.UIkit.notify({
                message: data.responseText,
                status: data.status,
                timeout: 3000,
                pos: 'top-center',
            })
        } catch (response) {
            const data = await response.json()
            window.UIkit.notify({
                message: data.error_message || data.detail,
                status: 'danger',
                timeout: 3000,
                pos: 'top-center',
            })
        }
    }

    /**
     * @param event
     * @param {HTMLElement} rootEl
     * @param {HTMLElement} el
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableFolderTreeChange(event, rootEl, el, status) {
        let element = $(el)
        /*
         * If folder removed, do not do anything, the other folderTree will be triggered
         */
        if (status === 'removed') {
            return false
        }

        let folderId = parseInt(element.attr('data-folder-id'))
        let parentFolderId = null

        if (element.parents('.foldertree-element').length) {
            parentFolderId = parseInt(element.parents('.foldertree-element').eq(0).attr('data-folder-id'))
        } else if (element.parents('.root-tree').length) {
            parentFolderId = parseInt(element.parents('.root-tree').eq(0).attr('data-parent-folder-id'))
        }

        /*
         * When dropping to route
         * set parentFolderId to NULL
         */
        if (isNaN(parentFolderId)) {
            parentFolderId = null
        }

        /*
         * User dragged folder inside itself
         * It will destroy the Internet !
         */
        if (folderId === parentFolderId) {
            console.error('You cannot move a folder inside itself!')
            alert('You cannot move a folder inside itself!')
            window.location.reload()
            return false
        }

        let postData = {
            _token: this.ajaxToken,
            _action: 'updatePosition',
            folderId: folderId,
            newParent: parentFolderId,
        }

        /*
         * Get folder siblings id to compute new position
         */
        if (element.next().length && typeof element.next().attr('data-folder-id') !== 'undefined') {
            postData.nextFolderId = parseInt(element.next().attr('data-folder-id'))
        } else if (element.prev().length && typeof element.prev().attr('data-folder-id') !== 'undefined') {
            postData.prevFolderId = parseInt(element.prev().attr('data-folder-id'))
        }

        try {
            const response = await fetch(this.routes.folderAjaxEdit.replace('%folderId%', folderId), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new URLSearchParams(postData),
            })
            if (!response.ok) {
                throw response
            }
            const data = await response.json()
            window.UIkit.notify({
                message: data.responseText,
                status: data.status,
                timeout: 3000,
                pos: 'top-center',
            })
        } catch (response) {
            const data = await response.json()
            window.UIkit.notify({
                message: data.error_message || data.detail,
                status: 'danger',
                timeout: 3000,
                pos: 'top-center',
            })
        }
    }

    /**
     * Back top click
     * @return {boolean} [description]
     */
    backTopBtnClick() {
        TweenLite.to(this.$mainContentScrollable, 0.6, { scrollTo: { y: 0 }, ease: Expo.easeOut })
        return false
    }

    /**
     * Resize
     * @return {[type]} [description]
     */
    resize() {
        this.windowWidth = this.$window.width()
        this.windowHeight = this.$window.height()

        // Close tree panel if small screen & first resize
        if (this.windowWidth >= 768 && this.windowWidth <= 1200 && this.$mainTrees.length && this.resizeFirst) {
            this.$mainTrees[0].style.display = 'none'
            this.$minifyTreePanelButton.trigger('click')
            window.requestAnimationFrame(() => {
                this.$mainTrees[0].style.display = 'table-cell'
            })
        }

        // Check if mobile
        if (this.windowWidth <= 768 && this.resizeFirst) {
            this.mobile = new RozierMobile()
        }

        if (this.$mainTreesContainer.length && this.$mainContentScrollable.length) {
            if (this.windowWidth >= 768) {
                this.$mainContentScrollable.height(this.windowHeight)
                this.$mainTreesContainer[0].style.height = ''
            } else {
                this.$mainContentScrollable[0].style.height = ''
                this.$mainTreesContainer.height(this.windowHeight)
            }
        }

        // Tree scroll height
        if (this.$mainTrees.length) {
            this.$nodeTreeHead = this.$mainTrees.find('.nodetree-head')
            this.$treeScrollCont = this.$mainTrees.find('.tree-scroll-cont')
            this.$treeScroll = this.$mainTrees.find('.tree-scroll')

            /*
             * need actual to get tree height even when they are hidden.
             */
            this.nodesSourcesSearchHeight = this.$nodesSourcesSearch.actual('outerHeight')
            this.nodeTreeHeadHeight = this.$nodeTreeHead.actual('outerHeight')
            this.treeScrollHeight = this.windowHeight - (this.nodesSourcesSearchHeight + this.nodeTreeHeadHeight)

            if (this.mobile !== null) {
                this.treeScrollHeight = this.windowHeight - (50 + 50 + this.nodeTreeHeadHeight)
            } // Menu + tree menu + tree head

            for (let i = 0; i < this.$treeScrollCont.length; i++) {
                this.$treeScrollCont[i].style.height = this.treeScrollHeight + 'px'
            }
        }

        // Main content
        this.mainContentScrollableWidth = this.$mainContentScrollable.width()
        this.mainContentScrollableOffsetLeft = this.windowWidth - this.mainContentScrollableWidth

        this.lazyload.resize()
        this.entriesPanel.replaceSubNavs()

        // Set resize first to false
        if (this.resizeFirst) this.resizeFirst = false
    }
}
