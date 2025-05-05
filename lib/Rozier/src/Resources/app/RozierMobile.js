import $ from 'jquery'
import { TweenLite, Expo } from 'gsap'
import { addClass, removeClass } from './utils/plugins'

/**
 * Rozier Mobile
 */
export default class RozierMobile {
    constructor() {
        // Selectors
        this.$menu = $('#menu-mobile')
        this.$adminMenu = $('#admin-menu')
        this.$adminMenuLink = this.$adminMenu.find('a')
        this.$adminMenuNavParent = this.$adminMenu.find('.uk-parent')

        this.$searchButton = $('#search-button')
        this.$searchPanel = $('#nodes-sources-search')
        this.$treeButton = $('#tree-button')
        this.$treeWrapper = $('#tree-wrapper')
        this.$treeWrapperLink = this.$treeWrapper.find('a')
        this.$userPicture = $('#user-picture')
        this.$userActions = $('.user-actions')
        this.$userActionsLink = this.$userActions.find('a')
        this.$mainContentOverlay = $('#main-content-overlay')

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
        if (this.$userPicture.length) {
            // Add class on user picture link to unbind default event
            addClass(this.$userPicture[0], 'rz-no-ajax-link')
        }
        // Events
        this.$menu.on('click', this.menuClick)
        this.$adminMenuLink.on('click', this.adminMenuLinkClick)
        this.$adminMenuNavParent.on('click', this.adminMenuNavParentClick)
        this.$searchButton.on('click', this.searchButtonClick)
        this.$treeButton.on('click', this.treeButtonClick)
        this.$treeWrapperLink.on('click', this.treeWrapperLinkClick)
        this.$userPicture.on('click', this.userPictureClick)
        this.$userActionsLink.on('click', this.userActionsLinkClick)
        this.$mainContentOverlay.on('click', this.mainContentOverlayClick)

        window.addEventListener('pageload', this.mainContentOverlayClick)
    }

    menuClick(e) {
        if (!this.menuOpen) this.openMenu()
        else this.closeMenu()
    }

    adminMenuNavParentClick(e) {
        let $target = $(e.currentTarget)
        let $ukNavSub = $(e.currentTarget).find('.uk-nav-sub')

        // Open
        if (!$target.hasClass('nav-open')) {
            let $ukNavSubItem = $ukNavSub.find('.uk-nav-sub-item')
            let ukNavSubHeight = $ukNavSubItem.length * 41 - 3

            $ukNavSub[0].style.display = 'block'
            TweenLite.to($ukNavSub, 0.6, { height: ukNavSubHeight, ease: Expo.easeOut, onComplete: function () {} })

            $target.addClass('nav-open')
        } else {
            // Close
            TweenLite.to($ukNavSub, 0.6, {
                height: 0,
                ease: Expo.easeOut,
                onComplete: function () {
                    $ukNavSub[0].style.display = 'none'
                },
            })

            $target.removeClass('nav-open')
        }
    }

    adminMenuLinkClick(e) {
        if (this.menuOpen) this.closeMenu()
    }

    showOverlay() {
        if (this.$mainContentOverlay.length) {
            this.$mainContentOverlay.addClass('open')
        }
    }

    hideOverlay() {
        if (this.$mainContentOverlay.length) {
            this.$mainContentOverlay.removeClass('open')
        }
    }

    openMenu() {
        // Close panel if open
        this.closeSearch()
        this.closeTree()
        this.closeUser()

        // Translate menu panel
        this.$adminMenu.addClass('open')
        this.showOverlay()
        this.menuOpen = true
    }

    closeMenu() {
        this.hideOverlay()
        this.$adminMenu.removeClass('open')
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
        this.$searchPanel.addClass('open')
        // Add active class
        this.$searchButton.addClass('active')
        this.searchOpen = true
    }

    closeSearch() {
        this.hideOverlay()

        // Remove active class
        this.$searchPanel.removeClass('open')
        this.$searchButton.removeClass('active')
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
        this.$treeWrapper.addClass('open')
        this.$treeButton.addClass('active')
        this.treeOpen = true
    }

    closeTree() {
        this.$treeWrapper.removeClass('open')
        this.hideOverlay()

        // Remove active class
        removeClass(this.$treeButton[0], 'active')

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
        this.$userActions.addClass('open')

        this.showOverlay()

        // Add active class
        this.$userPicture.addClass('active')
        this.userOpen = true
    }

    closeUser() {
        this.hideOverlay()

        // Remove active class
        this.$userActions.removeClass('open')
        this.$userPicture.removeClass('active')
        this.userOpen = false
    }

    mainContentOverlayClick(e) {
        this.closeMenu()
        this.closeTree()
        this.closeUser()
        this.closeSearch()
    }
}
