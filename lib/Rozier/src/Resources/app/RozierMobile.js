import {Expo, TweenLite} from 'gsap'
import {addClass, removeClass} from './utils/plugins'

/**
 * Rozier Mobile
 */
export default class RozierMobile {
    constructor() {
        // Selectors
        this.menu = document.getElementById('menu-mobile')
        this.adminMenu = document.getElementById('admin-menu')
        this.adminMenuLink = this.adminMenu.querySelector('a')
        this.adminMenuNavParent = this.adminMenu.querySelector('.uk-parent')

        this.searchButton = document.getElementById('#search-button')
        this.searchPanel = document.getElementById('#nodes-sources-search')
        this.treeButton = document.getElementById('#tree-button')
        this.treeWrapper = document.getElementById('#tree-wrapper')
        this.treeWrapperLink = this.treeWrapper.querySelector('a')
        this.userPicture = document.getElementById('#user-picture')
        this.userActions = document.querySelector('.user-actions')
        this.userActionsLink = this.userActions.querySelector('a')
        this.mainContentOverlay = document.getElementById('#main-content-overlay')

        this.menuOpen = false
        this.searchOpen = false
        this.treeOpen = false
        this.adminOpen = false

        this.menuClick = this.menuClick.bind(this)
        this.adminMenuLinkClick = this.adminMenuLinkClick.bind(this)
        this.adminMenuNavParentClick = this.adminMenuNavParentClick.bind(this)
        this.searchButtonClick = this.searchButtonClick.bind(this)
        this.treeButtonClick = this.treeButtonClick.bind(this)
        this.treeWrapperLinkClick = this.treeWrapperLinkClick.bind(this)
        this.userPictureClick = this.userPictureClick.bind(this)
        this.userActionsLinkClick = this.userActionsLinkClick.bind(this)
        this.mainContentOverlayClick = this.mainContentOverlayClick.bind(this)

        // Methods
        this.init()
    }

    init() {
        if (this.userPicture.length) {
            // Add class on user picture link to unbind default event
            addClass(this.userPicture[0], 'rz-no-ajax-link')
        }
        // Events
        this.menu.addEventListener('click', this.menuClick)
        this.adminMenuLink.addEventListener('click', this.adminMenuLinkClick)
        this.adminMenuNavParent.addEventListener('click', this.adminMenuNavParentClick)
        this.searchButton.addEventListener('click', this.searchButtonClick)
        this.treeButton.addEventListener('click', this.treeButtonClick)
        this.treeWrapperLink.addEventListener('click', this.treeWrapperLinkClick)
        this.userPicture.addEventListener('click', this.userPictureClick)
        this.userActionsLink.addEventListener('click', this.userActionsLinkClick)
        this.mainContentOverlay.addEventListener('click', this.mainContentOverlayClick)

        window.addEventListener('pageload', this.mainContentOverlayClick)
    }

    menuClick(e) {
        if (!this.menuOpen) this.openMenu()
        else this.closeMenu()
    }

    adminMenuNavParentClick(e) {
        let target = e.currentTarget
        let ukNavSub = target.querySelectorAll('.uk-nav-sub')

        // Open
        if (!target.classList.contains('nav-open')) {
            let ukNavSubItem = ukNavSub.querySelectorAll('.uk-nav-sub-item')
            let ukNavSubHeight = ukNavSubItem.length * 41 - 3

            ukNavSub[0].style.display = 'block'
            TweenLite.to(ukNavSub, 0.6, { height: ukNavSubHeight, ease: Expo.easeOut, onComplete: function () {} })

            target.classList.add('nav-open')
        } else {
            // Close
            TweenLite.to(ukNavSub, 0.6, {
                height: 0,
                ease: Expo.easeOut,
                onComplete: function () {
                    ukNavSub[0].style.display = 'none'
                },
            })

            target.classList.remove('nav-open')
        }
    }

    adminMenuLinkClick(e) {
        if (this.menuOpen) this.closeMenu()
    }

    showOverlay() {
        if (this.mainContentOverlay.length) {
            this.mainContentOverlay.classList.add('open')
        }
    }

    hideOverlay() {
        if (this.mainContentOverlay.length) {
            this.mainContentOverlay.classList.remove('open')
        }
    }

    openMenu() {
        // Close panel if open
        this.closeSearch()
        this.closeTree()
        this.closeUser()

        // Translate menu panel
        this.adminMenu.classList.add('open')
        this.showOverlay()
        this.menuOpen = true
    }

    closeMenu() {
        this.hideOverlay()
        this.adminMenu.classList.remove('open')
        this.menuOpen = false
    }

    searchButtonClick(e) {
        if (!this.searchOpen) this.openSearch()
        else this.closeSearch()
    }

    openSearch() {
        // Close panel if open
        this.closeMenu()
        this.closeTree()
        this.closeUser()

        this.showOverlay()

        // Translate search panel
        this.searchPanel.classList.add('open')
        // Add active class
        this.searchButton.classList.add('active')
        this.searchOpen = true
    }

    closeSearch() {
        this.hideOverlay()

        // Remove active class
        this.searchPanel.classList.remove('open')
        this.searchButton.classList.remove('active')
        this.searchOpen = false
    }

    treeButtonClick(e) {
        if (!this.treeOpen) this.openTree()
        else this.closeTree()
    }

    treeWrapperLinkClick(e) {
        if (e.currentTarget.className.indexOf('tab-link') === -1 && this.treeOpen) {
            this.closeTree()
        }
    }

    /**
     * Open tree
     * @return {[type]} [description]
     */
    openTree() {
        // Close panel if open
        this.closeMenu()
        this.closeSearch()
        this.closeUser()

        this.showOverlay()

        // Add active class
        this.treeWrapper.classList.add('open')
        this.treeButton.classList.add('active')
        this.treeOpen = true
    }

    closeTree() {
        this.treeWrapper.classList.remove('open')
        this.hideOverlay()

        // Remove active class
        removeClass(this.treeButton, 'active')

        this.treeOpen = false
    }

    userPictureClick(e) {
        if (!this.userOpen) this.openUser()
        else this.closeUser()
        return false
    }

    userActionsLinkClick(e) {
        if (this.userOpen) {
            this.closeUser()
        }
    }

    openUser() {
        // Close panel if open
        this.closeMenu()
        this.closeSearch()
        this.closeTree()

        // Translate user panel
        this.userActions.classList.add('open')

        this.showOverlay()

        // Add active class
        this.userPicture.classList.add('active')
        this.userOpen = true
    }

    closeUser() {
        this.hideOverlay()

        // Remove active class
        this.userActions.classList.remove('open')
        this.userPicture.classList.remove('active')
        this.userOpen = false
    }

    mainContentOverlayClick(e) {
        this.closeMenu()
        this.closeTree()
        this.closeUser()
        this.closeSearch()
    }
}
