import Lazyload from './Lazyload'
import EntriesPanel from './components/panels/EntriesPanel'
import VueApp from './App'
import { Expo, TweenLite } from 'gsap'
import NodeTreeContextActions from './components/trees/NodeTreeContextActions'
import RozierMobile from './RozierMobile'
import bulkActions from './widgets/GenericBulkActions'
import colorInputs from './widgets/ColorInput'
import { fadeIn, fadeOut } from './utils/animation'
import { sleep } from './utils/sleep'
import 'gsap/ScrollToPlugin'

/**
 * Rozier root entry
 */
export default class Rozier {
    constructor() {
        this.windowWidth = null
        this.windowHeight = null
        this.resizeFirst = true
        this.mobile = null

        this.minifyTreePanelButton = null
        this.mainTrees = null

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
         * override default nestable settings in order to
         * store toggle state between reloads.
         */
        this.setupCollapsedNestableState()

        this.lazyload = new Lazyload()
        this.entriesPanel = new EntriesPanel()
        this.vueApp = new VueApp()
        this.mobile = new RozierMobile()

        // --- Selectors --- //
        this.minifyTreePanelButton = document.querySelectorAll('#minify-tree-panel-button')
        this.mainTrees = document.querySelector('#main-trees')
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
        colorInputs()
        window.addEventListener('pageshowend', () => {
            bulkActions()
            colorInputs()
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
        /*
         * ui kit events require jQuery objects
         */
        const nodeTrees = document.querySelectorAll('.nodetree-widget .root-tree')
        nodeTrees.forEach((nodeTree) => {
            const $nodeTree = $(nodeTree)
            $nodeTree.off('change.uk.nestable', this.onNestableNodeTreeChange)
            $nodeTree.on('change.uk.nestable', this.onNestableNodeTreeChange)
        })

        const tagTrees = document.querySelectorAll('.tagtree-widget .root-tree')
        tagTrees.forEach((tagTree) => {
            const $tagTree = $(tagTree)
            $tagTree.off('change.uk.nestable', this.onNestableTagTreeChange)
            $tagTree.on('change.uk.nestable', this.onNestableTagTreeChange)
        })

        const folderTrees = document.querySelectorAll('.foldertree-widget .root-tree')
        folderTrees.forEach((folderTree) => {
            const $folderTree = $(folderTree)
            $folderTree.off('change.uk.nestable', this.onNestableFolderTreeChange)
            $folderTree.on('change.uk.nestable', this.onNestableFolderTreeChange)
        })

        // Contextual menu on tree element names
        const mainTreeElementName = this.mainTrees.querySelectorAll('.tree-element-name')
        mainTreeElementName.forEach((element) => {
            element.removeEventListener('contextmenu', this.maintreeElementNameRightClick)
            element.addEventListener('contextmenu', this.maintreeElementNameRightClick)
        })
    }

    /**
     * Main tree element name right click.
     * @return {boolean}
     */
    maintreeElementNameRightClick(e) {
        e.preventDefault()

        // Close all other contextual menus
        document.querySelectorAll('.tree-contextualmenu').forEach((contextualMenu) => {
            if (contextualMenu.classList.contains('uk-open')) {
                contextualMenu.classList.remove('uk-open')
            }
        })

        const contextualMenu = e.currentTarget.parentElement.querySelector('.tree-contextualmenu')
        if (contextualMenu) {
            if (!contextualMenu.classList.contains('uk-open')) {
                contextualMenu.classList.add('uk-open')
            } else {
                contextualMenu.classList.remove('uk-open')
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
            const target = event.target.closest('#tree-container .nodetree-langs a')
            if (target) {
                this.lazyload.canvasLoader.show()
                const translationId = parseInt(target.getAttribute('data-translation-id'), 10)
                this.refreshMainNodeTree(translationId)
                return false
            }
        })
    }

    fetchSessionMessages() {
        return new Promise(async (resolve, reject) => {
            const query = new URLSearchParams({
                _action: 'messages',
                _token: window.RozierConfig.ajaxToken,
            })
            const url = window.RozierConfig.routes.ajaxSessionMessages + '?' + query.toString()
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
                    resolve([])
                }
                resolve(data.messages)
            } catch (e) {
                reject(e)
            }
        })
    }
    /**
     * Get messages.
     */
    async getMessages() {
        const messages = await this.fetchSessionMessages()
        if (messages.confirm && Array.isArray(messages.confirm) && messages.confirm.length > 0) {
            messages.confirm.forEach((message) => {
                window.UIkit.notify({
                    message: message,
                    status: 'success',
                    timeout: 2000,
                    pos: 'top-center',
                })
            })
        }
        if (messages.error && Array.isArray(messages.error) && messages.error.length > 0) {
            messages.error.forEach((message) => {
                window.UIkit.notify({
                    message: message,
                    status: 'error',
                    timeout: 2000,
                    pos: 'top-center',
                })
            })
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
            this.lazyload.childrenNodesFields.nodeTrees.forEach((nodeTree) => {
                promises.push(this.lazyload.childrenNodesFields.refreshNodeTree(nodeTree))
            })
        }
        return Promise.all(promises)
    }

    /**
     * Refresh only main nodeTree.
     *
     * @param {Number|null|undefined} translationId
     */
    async refreshMainNodeTree(translationId = undefined) {
        const currentNodeTree = document.querySelector('#tree-container .nodetree-widget')
        if (!currentNodeTree) {
            console.debug('No main node-tree available.')
            return
        }

        const currentRootTree = currentNodeTree.querySelector('.root-tree')
        if (currentRootTree && !translationId) {
            translationId = currentRootTree.getAttribute('data-translation-id')
        }

        try {
            const query = new URLSearchParams({
                _token: window.RozierConfig.ajaxToken,
                _action: 'requestMainNodeTree',
                translationId: translationId || null,
            })
            const url = window.RozierConfig.routes.nodesTreeAjax + '?' + query.toString()
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
                const newNodeTree = document.querySelector('#tree-container .nodetree-widget')
                if (newNodeTree) {
                    await fadeIn(newNodeTree)
                } else {
                    console.debug('No main node-tree available.')
                    return
                }
                this.initNestables()
                this.bindMainTrees()
                this.resize()
                this.lazyload.bindAjaxLink()
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
        const currentTagTree = document.querySelector('#tree-container .tagtree-widget')
        if (!currentTagTree) {
            console.debug('No main tag-tree available.')
            return
        }

        const query = new URLSearchParams({
            _token: window.RozierConfig.ajaxToken,
            _action: 'requestMainTagTree',
        })
        const url = window.RozierConfig.routes.tagsTreeAjax + '?' + query.toString()
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
            const newTagTree = document.querySelector('#tree-container .tagtree-widget')
            if (newTagTree) {
                await fadeIn(newTagTree)
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
        const currentFolderTree = document.querySelector('#tree-container .foldertree-widget')
        if (!currentFolderTree) {
            console.debug('No main folder-tree available.')
            return
        }

        const query = new URLSearchParams({
            _token: window.RozierConfig.ajaxToken,
            _action: 'requestMainFolderTree',
        })
        const url = window.RozierConfig.routes.foldersTreeAjax + '?' + query.toString()
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
            const newFolderTree = document.querySelector('#tree-container .foldertree-widget')
            if (newFolderTree) {
                await fadeIn(newFolderTree)
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
     * @param {jQuery} rootEl
     * @param {jQuery} $element
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableNodeTreeChange(event, rootEl, $element, status) {
        /*
         * If node removed, do not do anything, the other change.uk.nestable nodeTree will be triggered
         */
        if (status === 'removed') {
            return false
        }
        if (!$element) {
            return false
        }
        const element = $element[0]
        if (!element) {
            console.error('No element found')
            return false
        }
        const nodeId = parseInt(element.getAttribute('data-node-id'))
        let parentNodeId = null
        const nodeTree = element.parentElement.closest('.nodetree-element')
        const stackTree = element.parentElement.closest('.stack-tree-widget')
        const childrenNodetree = element.parentElement.closest('.children-node-widget')

        if (nodeTree) {
            parentNodeId = parseInt(nodeTree.getAttribute('data-node-id'))
        } else if (stackTree) {
            parentNodeId = parseInt(stackTree.getAttribute('data-parent-node-id'))
        } else if (childrenNodetree) {
            parentNodeId = parseInt(childrenNodetree.getAttribute('data-parent-node-id'))
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
            _token: window.RozierConfig.ajaxToken,
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
            const route = window.RozierConfig.routes.nodeAjaxEdit.replace('%nodeId%', nodeId)
            const response = await fetch(route, {
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
     * @param {jQuery} rootEl
     * @param {jQuery} $element
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableTagTreeChange(event, rootEl, $element, status) {
        /*
         * If tag removed, do not do anything, the other tagTree will be triggered
         */
        if (status === 'removed') {
            return false
        }

        const element = $element[0]
        let tagId = parseInt(element.getAttribute('data-tag-id'))
        let parentTagId = null
        const tagTree = element.parentElement.closest('.tagtree-element')
        const rootTree = element.parentElement.closest('.root-tree')
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
            _token: window.RozierConfig.ajaxToken,
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
            const response = await fetch(window.RozierConfig.routes.tagAjaxEdit.replace('%tagId%', tagId), {
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
     * @param {jQuery} rootEl
     * @param {jQuery} $element
     * @param {string|null|undefined} status
     * @returns {false|undefined}
     */
    async onNestableFolderTreeChange(event, rootEl, $element, status) {
        /*
         * If folder removed, do not do anything, the other folderTree will be triggered
         */
        if (status === 'removed') {
            return false
        }
        const element = $element[0]
        let folderId = parseInt(element.getAttribute('data-folder-id'))
        let parentFolderId = null

        const folderTreeElement = element.parentElement.closest('.foldertree-element')
        const rootTreeElement = element.parentElement.closest('.root-tree')
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
            _token: window.RozierConfig.ajaxToken,
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
            const response = await fetch(window.RozierConfig.routes.folderAjaxEdit.replace('%folderId%', folderId), {
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

    backTopBtnClick() {
        TweenLite.to(this.mainContentScrollable, 0.6, { scrollTo: { y: 0 }, ease: Expo.easeOut })
        return false
    }

    resize() {
        this.windowWidth = window.offsetWidth
        this.windowHeight = window.offsetHeight

        // this.lazyload.resize()
        this.entriesPanel.replaceSubNavs()

        // Set resize first to false
        if (this.resizeFirst) this.resizeFirst = false
    }
}
