import Lazyload from './Lazyload'
import EntriesPanel from './components/panels/EntriesPanel'
import VueApp from './App'
import {Expo, TweenLite} from 'gsap'
import NodeTreeContextActions from './components/trees/NodeTreeContextActions'
import RozierMobile from './RozierMobile'
import bulkActions from './widgets/GenericBulkActions'
import {fadeIn, fadeOut} from "./utils/animation";
import { sleep } from "./utils/sleep";

require('gsap/ScrollToPlugin')
/**
 * Rozier root entry
 */
export default class Rozier {
    constructor() {
        this.windowWidth = null
        this.windowHeight = null
        this.resizeFirst = true
        this.mobile = null
        this.ajaxToken = null

        this.nodeTrees = []
        this.treeTrees = []

        this.userPanelContainer = null
        this.minifyTreePanelButton = null
        this.mainTrees = null
        this.mainTreesContainer = null
        this.mainTreeElementName = null
        this.$treeContextualButton = null
        this.nodesSourcesSearch = null
        this.nodesSourcesSearchHeight = null
        this.$nodeTreeHead = null
        this.nodeTreeHeadHeight = null
        this.$treeScrollCont = null
        this.$treeScroll = null
        this.treeScrollHeight = null

        this.mainContentScrollable = null
        this.mainContentScrollableWidth = null
        this.mainContentScrollableOffsetLeft = null
        this.backTopBtn = null
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

        // --- Selectors --- //
        this.userPanelContainer = document.querySelectorAll('#user-panel-container')
        this.minifyTreePanelButton = document.querySelectorAll('#minify-tree-panel-button')
        this.mainTrees = document.querySelector('#main-trees')
        this.mainTreesContainer = document.querySelector('#main-trees-container')
        this.nodesSourcesSearch = document.querySelector('#nodes-sources-search')
        this.mainContentScrollable = document.querySelector('#main-content-scrollable')
        this.backTopBtn = document.querySelector('#back-top-button')

        // Minify trees panel toggle button
        if (this.minifyTreePanelButton.length) {
            this.minifyTreePanelButton.forEach((element) => {
                element.addEventListener('click', this.toggleTreesPanel)
            })
        }

        document.body.addEventListener('markdownPreviewOpen', this.openTreesPanel, false)

        // Back top btn
        this.backTopBtn.addEventListener('click', this.backTopBtnClick)

        window.addEventListener('resize', this.resize)
        this.resize()

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
        const nodeTree = document.querySelector('.nodetree-widget .root-tree');
        if (nodeTree) {
            nodeTree.removeEventListener('change.uk.nestable', this.onNestableNodeTreeChange);
            nodeTree.addEventListener('change.uk.nestable', this.onNestableNodeTreeChange);
        }

        const tagTree = document.querySelector('.tagtree-widget .root-tree');
        if (tagTree) {
            tagTree.removeEventListener('change.uk.nestable', this.onNestableTagTreeChange);
            tagTree.addEventListener('change.uk.nestable', this.onNestableTagTreeChange);
        }

        const folderTree = document.querySelector('.foldertree-widget .root-tree');
        if (folderTree) {
            folderTree.removeEventListener('change.uk.nestable', this.onNestableFolderTreeChange);
            folderTree.addEventListener('change.uk.nestable', this.onNestableFolderTreeChange);
        }

        // Tree element name
        this.mainTreeElementName = this.mainTrees.querySelectorAll('.tree-element-name')
        if (this.mainTreeElementName.length) {
            this.mainTreeElementName.forEach((element) => {
                element.removeEventListener('contextmenu', this.maintreeElementNameRightClick)
                element.addEventListener('contextmenu', this.maintreeElementNameRightClick)
            })
        }
    }

    /**
     * Main tree element name right click.
     * @return {boolean}
     */
    maintreeElementNameRightClick(e) {
        const contextualMenu = e.currentTarget.parentElement.querySelector('.tree-contextualmenu');
        if (contextualMenu) {
            if (!contextualMenu.classList.contains('uk-open')) {
                contextualMenu.classList.add('uk-open');
            } else {
                contextualMenu.classList.remove('uk-open');
            }
        }

        return false
    }

    /**
     * Bind main node tree langs.
     *
     * @return {boolean}
     */
    bindMainNodeTreeLangs() {
        document.body.addEventListener('click', (event) => {
            const target = event.target.closest('#tree-container .nodetree-langs a');
            if (target) {
                this.lazyload.canvasLoader.show();
                const translationId = parseInt(target.getAttribute('data-translation-id'), 10);
                this.refreshMainNodeTree(translationId);
                return false
            }
        });
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
        let currentNodeTree = document.getElementById('tree-container').querySelector('.nodetree-widget')
        if (!currentNodeTree) {
            console.debug('No main node-tree available.')
            return
        }

        let currentRootTree = currentNodeTree.querySelector('.root-tree')
        if (currentRootTree && !translationId) {
            translationId = currentRootTree.getAttribute('data-translation-id')
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
                await fadeOut(currentNodeTree)
                currentNodeTree.insertAdjacentHTML('afterend', data.nodeTree)
                currentNodeTree.remove()

                currentNodeTree = document.getElementById('tree-container').querySelector('.nodetree-widget')
                if (currentNodeTree) {
                    await fadeIn(currentNodeTree)
                } else {
                    console.debug('No main node-tree available.')
                    return
                }
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
            await sleep(3000)
            this.refreshMainNodeTree(translationId)
        }

        this.lazyload.canvasLoader.hide()
    }

    /**
     * Refresh only main tagTree.
     *
     */
    async refreshMainTagTree() {
        let currentTagTree = document.getElementById('tree-container').querySelector('.tagtree-widget')

        if (!currentTagTree) {
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
            await fadeOut(currentTagTree)
            currentTagTree.insertAdjacentHTML('afterend', data.tagTree)
            currentTagTree.remove()
            currentTagTree = document.getElementById(
                'tree-container').querySelector('.tagtree-widget')
            if (currentTagTree) {
                await fadeIn(currentTagTree)
            } else {
                console.debug('No main tag-tree available.')
                return
            }
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
        let currentFolderTree = document.getElementById('tree-container').querySelector('.foldertree-widget')

        if (!currentFolderTree) {
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
            await fadeOut(currentFolderTree)
            currentFolderTree.insertAdjacentHTML('afterend', data.folderTree)
            currentFolderTree.remove()
            currentFolderTree = document.getElementById('tree-container').querySelector('.foldertree-widget')
            if (currentFolderTree) {
                await fadeIn(currentFolderTree)
            } else {
                console.debug('No main folder-tree available.')
                return
            }
            this.initNestables()
            this.bindMainTrees()
            this.resize()
            this.lazyload.bindAjaxLink()
        }

        this.lazyload.canvasLoader.hide()
    }

    /**
     * Toggle trees panel
     */
    toggleTreesPanel() {
        document.getElementById('main-container-inner').classList.toggle('trees-panel--minified')
        document.getElementById('main-content').classList.toggle('maximized')
        document.querySelector('#minify-tree-panel-button i').classList.toggle('uk-icon-rz-panel-tree-open')
        document.getElementById('minify-tree-panel-area').classList.toggle('tree-panel-hidden')

        return false
    }

    openTreesPanel() {
        if (document.getElementById('main-container-inner').classList.contains('trees-panel--minified')) {
            this.toggleTreesPanel()
        }

        return false
    }

    /**
     * Toggle user panel
     */
    toggleUserPanel() {
        document.getElementById('user-panel').classList.toggle('minified')
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
     * @param {HTMLElement} element
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableNodeTreeChange(event, rootEl, element, status) {
        /*
         * If node removed, do not do anything, the other change.uk.nestable nodeTree will be triggered
         */
        if (status === 'removed') {
            return false
        }
        let nodeId = parseInt(element.getAttribute('data-node-id'))
        let parentNodeId = null
        if (element.closest('.nodetree-element')) {
            parentNodeId = parseInt(element.closest('.nodetree-element').getAttribute('data-node-id'))
        } else if (element.closest('.stack-tree-widget')) {
            parentNodeId = parseInt(element.closest('.stack-tree-widget').getAttribute('data-parent-node-id'))
        } else if (element.closest('.children-node-widget')) {
            parentNodeId = parseInt(element.closest('.children-node-widget').getAttribute('data-parent-node-id'))
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
        const nextElement = element.nextElementSibling
        if (nextElement && typeof nextElement.getAttribute('data-node-id') !== 'undefined') {
            postData.nextNodeId = parseInt(nextElement.getAttribute('data-node-id'))
        } else {
            const previousElement = element.previousElementSibling
            if (previousElement && typeof previousElement.getAttribute('data-node-id') !== 'undefined') {
                postData.prevNodeId = parseInt(previousElement.getAttribute('data-node-id'))
            }
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
     * @param {HTMLElement} element
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableTagTreeChange(event, rootEl, element, status) {
        /*
         * If tag removed, do not do anything, the other tagTree will be triggered
         */
        if (status === 'removed') {
            return false
        }

        let tagId = parseInt(element.getAttribute('data-tag-id'))
        let parentTagId = null
        const tagTree = element.closest('.tagtree-element')
        const rootTree = element.closest('.root-tree')
        if (tagTree) {
            parentTagId = parseInt(tagTree.getAttribute('data-tag-id'))
        } else if (rootTree) {
            parentTagId = parseInt(rootTree.getAttribute('data-parent-tag-id'))
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
        const nextElement = element.nextElementSibling
        if (nextElement && typeof nextElement.getAttribute('data-tag-id') !== 'undefined') {
            postData.nextTagId = parseInt(nextElement.getAttribute('data-tag-id'))
        } else {
            const previousElement = element.previousElementSibling
            if (previousElement && typeof previousElement.getAttribute('data-tag-id') !== 'undefined') {
                postData.prevTagId = parseInt(previousElement.getAttribute('data-tag-id'))
            }
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
     * @param {HTMLElement} element
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableFolderTreeChange(event, rootEl, element, status) {
        /*
         * If folder removed, do not do anything, the other folderTree will be triggered
         */
        if (status === 'removed') {
            return false
        }

        let folderId = parseInt(element.getAttribute('data-folder-id'))
        let parentFolderId = null

        const folderTreeElement = element.closest('.foldertree-element')
        const rootTreeElement = element.closest('.root-tree')
        if (folderTreeElement) {
            parentFolderId = parseInt(folderTreeElement.getAttribute('data-folder-id'))
        } else if (rootTreeElement) {
            parentFolderId = parseInt(rootTreeElement.getAttribute('data-parent-folder-id'))
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
        const nextElement = element.nextElementSibling
        if (nextElement && typeof nextElement.getAttribute('data-folder-id') !== 'undefined') {
            postData.nextFolderId = parseInt(nextElement.getAttribute('data-folder-id'))
        } else {
            const previousElement = element.previousElementSibling
            if (previousElement && typeof previousElement.getAttribute('data-folder-id') !== 'undefined') {
                postData.prevFolderId = parseInt(previousElement.getAttribute('data-folder-id'))
            }
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
        TweenLite.to(this.mainContentScrollable, 0.6, { scrollTo: { y: 0 }, ease: Expo.easeOut })
        return false
    }

    /**
     * Resize
     * @return {[type]} [description]
     */
    resize() {
        this.windowWidth = window.offsetWidth
        this.windowHeight = window.offsetHeight

        // Close tree panel if small screen & first resize
        if (this.windowWidth >= 768 && this.windowWidth <= 1200 && this.mainTrees && this.resizeFirst) {
            this.mainTrees.style.display = 'none'
            this.minifyTreePanelButton.click()
            window.requestAnimationFrame(() => {
                this.mainTrees.style.display = 'table-cell'
            })
        }

        // Check if mobile
        if (this.windowWidth <= 768 && this.resizeFirst) {
            this.mobile = new RozierMobile()
        }

        // this.lazyload.resize()
        this.entriesPanel.replaceSubNavs()

        // Set resize first to false
        if (this.resizeFirst) this.resizeFirst = false
    }
}
