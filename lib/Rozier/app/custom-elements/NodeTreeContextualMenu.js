export default class NodeTreeContextualMenu extends HTMLElement {
  get contextualMenuPath() {
    return this.getAttribute('data-contextual-menu-path')
  }

  get editPath() {
    return this.getAttribute('data-node-edit-path')
  }

  get statusPath() {
    return this.getAttribute('data-node-status-path')
  }

  connectedCallback() {
    const button = this.querySelector('.tree-contextualmenu-button')
    const route = this.contextualMenuPath

    if (!button || !route) {
      return
    }

    this.onClick = this.onClick.bind(this)
    this.moveNodeToPosition = this.moveNodeToPosition.bind(this)

    button.addEventListener('click', async () => {
      const contextualMenuNav = this.querySelector('nav.uk-dropdown')

      /*
             * Fetch contextual menu DOM if not already present
             */
      if (!contextualMenuNav) {
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))
        const contextualMenuDom = await fetch(route, {
          headers: {
            // Required to prevent using this route as referer when login again
            'X-Requested-With': 'XMLHttpRequest',
          },
        })
        this.insertAdjacentHTML('beforeend', await contextualMenuDom.text())
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
      }

      const actions = this.querySelectorAll('.node-actions a, .node-actions button, .duplicate-node')
      const nodeMoveFirstLink = this.querySelector('.move-node-first-position')
      const nodeMoveLastLink = this.querySelector('.move-node-last-position')

      actions.forEach((action) => {
        action.removeEventListener('click', this.onClick)
        action.addEventListener('click', this.onClick)
      })
      if (nodeMoveFirstLink) {
        nodeMoveFirstLink.addEventListener('click', e => this.moveNodeToPosition('first', e))
      }
      if (nodeMoveLastLink) {
        nodeMoveLastLink.addEventListener('click', e => this.moveNodeToPosition('last', e))
      }
    })
  }

  async onClick(event) {
    event.preventDefault()

    const element = event.currentTarget
    const nodeTreeElement = element.closest('.nodetree-element')
    const nodeId = parseInt(nodeTreeElement.getAttribute('data-node-id'))
    const statusName = element.getAttribute('data-status')
    const statusValue = element.getAttribute('data-value')
    const action = element.getAttribute('data-action')

    window.dispatchEvent(new CustomEvent('requestLoaderShow'))

    if (action === 'duplicate') {
      await this.duplicateNode(nodeId)
      return
    }

    if (statusName !== '' && statusValue !== '') {
      // Change node status
      await this.changeStatus(nodeId, statusName, statusValue)
      return
    }
  }

  async changeStatus(nodeId, statusName, statusValue) {
    try {
      await this.postNodeUpdate(this.statusPath, {
        _token: window.RozierConfig.ajaxToken,
        _action: 'nodeChangeStatus',
        nodeId: nodeId,
        statusName: statusName,
        statusValue: statusValue,
      })
    }
    finally {
      window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }
  }

  /**
     * Move a node to the position.
     *
     * @param nodeId
     */
  async duplicateNode(nodeId) {
    try {
      await this.postNodeUpdate(this.editPath, {
        _token: window.RozierConfig.ajaxToken,
        _action: 'duplicate',
        nodeId: nodeId,
      })
    }
    finally {
      window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }
  }

  /**
     * Move a node to the position.
     *
     * @param {String} position
     * @param {Event} event
     */
  async moveNodeToPosition(position, event) {
    window.dispatchEvent(new CustomEvent('requestLoaderShow'))

    let element = event.currentTarget.closest('.nodetree-element')
    let nodeId = parseInt(element.getAttribute('data-node-id'))
    let parentNodeId = parseInt(element.closest('ul').getAttribute('data-parent-node-id'))
    let postData = {
      _token: window.RozierConfig.ajaxToken,
      _action: 'updatePosition',
      nodeId: nodeId,
    }

    /*
         * Force to first position
         */
    if (typeof position !== 'undefined' && position === 'first') {
      postData.firstPosition = true
    }
    else if (typeof position !== 'undefined' && position === 'last') {
      postData.lastPosition = true
    }

    /*
         * When dropping to root
         * set parentNodeId to NULL
         */
    if (isNaN(parentNodeId)) {
      parentNodeId = null
    }

    postData.newParent = parentNodeId

    try {
      await this.postNodeUpdate(this.editPath, postData)
    }
    finally {
      window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }
  }

  async postNodeUpdate(url, postData) {
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          // Required to prevent using this route as referer when login again
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams(postData),
      })
      if (!response.ok) {
        const data = await response.json()
        window.dispatchEvent(
          new CustomEvent('pushToast', {
            detail: {
              message: data.error_message,
              status: 'danger',
            },
          }),
        )
        throw data
      }
      else {
        const data = await response.json()
        window.dispatchEvent(new CustomEvent('requestAllNodeTreeChange'))
        return data
      }
    }
    catch (error) {
      window.dispatchEvent(
        new CustomEvent('pushToast', {
          detail: {
            message: error.detail || error.message || 'postNodeUpdate: Unknown error',
            status: 'danger',
          },
        }))
    }
  }
}
