import { getResponseErrorMessage } from '../utils'

export default class StackNodeTree {
    constructor() {
        this.page = document.querySelector('.stack-tree')
        if (this.page) {
            this.quickAddNodeButtons = this.page.querySelectorAll(
                '.stack-tree-quick-creation button',
            )
        }

        this.onQuickAddClick = this.onQuickAddClick.bind(this)
        this.init()
    }

    /**
     * @return {Number|null}
     */
    getTranslationId() {
        const nodeTree = this.page.querySelector('.rz-tree__list')
        const translationId = parseInt(
            nodeTree.getAttribute('data-translation-id'),
        )

        return isNaN(translationId) ? null : translationId
    }

    init() {
        if (!this.page) return

        this.quickAddNodeButtons.forEach((btn) => {
            btn.addEventListener('click', this.onQuickAddClick)
        })
    }

    unbind() {
        if (!this.page) return

        this.quickAddNodeButtons.forEach((btn) => {
            btn.removeEventListener('click', this.onQuickAddClick)
        })
    }

    async quickAddNode(nodeTypeName, parentNodeId, tagId = null) {
        const params = {
            csrfToken: window.RozierConfig.ajaxToken,
            nodeTypeName,
            parentNodeId,
            pushTop: 1,
        }

        const translationId = this.getTranslationId()
        if (translationId !== null) {
            params.translationId = translationId
        }
        if (tagId !== null) {
            params.tagId = tagId
        }
        const response = await fetch(
            window.RozierConfig.routes.nodesGenerateAndAddNodeAction,
            {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    // Required to prevent using this route as referer when login again
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams(params),
            },
        )
        if (!response.ok) throw await response.json()
        return await response.json()
    }

    async refreshTree() {
        await this.page.querySelector('rz-tree').refreshNodeTree()
    }

    async onQuickAddClick(event) {
        event.preventDefault()
        const link = event.currentTarget
        const nodeTypeName = link.getAttribute('data-children-node-type')
        const parentNodeId = parseInt(
            link.getAttribute('data-children-parent-node'),
        )
        let tagId = null

        if (!nodeTypeName || parentNodeId <= 0) return false

        if (link.hasAttribute('data-filter-tag')) {
            tagId = parseInt(link.getAttribute('data-filter-tag'))
        }

        try {
            await this.quickAddNode(nodeTypeName, parentNodeId, tagId)
            await this.refreshTree()
            await window.Rozier.getMessages()
        } catch (data) {
            console.error('Error while quick adding node', data)
            if (data.violations?.length) {
                data.violations.forEach((violation) => {
                    window.dispatchEvent(
                        new CustomEvent('pushToast', {
                            detail: {
                                title: data.title,
                                message:
                                    violation.title || violation.propertyPath,
                                status: 'danger',
                            },
                        }),
                    )
                })
            } else {
                window.dispatchEvent(
                    new CustomEvent('pushToast', {
                        detail: {
                            message: getResponseErrorMessage(data),
                            status: 'danger',
                        },
                    }),
                )
            }
        }
    }
}
