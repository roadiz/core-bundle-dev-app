/**
 * Node Statuses
 */
export default class NodeStatuses {
    constructor() {
        this.containers = document.querySelector('.node-statuses, .node-actions')
        if (!this.containers) {
            return
        }
        this.icon = document.querySelector('.node-status header i')
        this.inputs = this.containers.querySelector('input[type="checkbox"], input[type="radio"]')
        this.item = this.containers.querySelector('.node-statuses-item')
        this.locked = false

        this.itemClick = this.itemClick.bind(this)
        this.containerEnter = this.containerEnter.bind(this)
        this.containerLeave = this.containerLeave.bind(this)
        this.onChange = this.onChange.bind(this)

        if (this.inputs && this.item && this.containers) {
            this.init()
        }
    }

    init() {
        this.item.removeEventListener('click', this.itemClick)
        this.item.addEventListener('click', this.itemClick)

        this.containers.addEventListener('mouseenter', this.containerEnter)
        this.containers.addEventListener('mouseleave', this.containerLeave)

        this.inputs.removeEventListener('change', this.onChange)
        this.inputs.addEventListener('change', this.onChange)

        // TODO: Create custom switcher for boolean checkboxes
        // this.$containers.find('.rz-boolean-checkbox').bootstrapSwitch({
        //     size: 'small',
        //     onSwitchChange: this.onChange,
        // })
        this.containers.querySelectorAll('.rz-boolean-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.onChange)
        })
    }

    containerEnter(event) {
        event.stopPropagation()

        let container = event.currentTarget
        let list = container.querySelector('ul, nav')
        let containerHeight = container.offsetHeight
        let listHeight = list.offsetHeight
        let containerOffsetTop = container.getBoundingClientRect().top + window.scrollY
        let windowHeight = window.innerHeight
        let fullHeight = containerOffsetTop + listHeight + containerHeight

        if (windowHeight < fullHeight) {
            container.classList.add('reverse')
        }
    }

    containerLeave(event) {
        event.stopPropagation()

        let container = event.currentTarget
        container.classList.remove('reverse')
    }

    itemClick(event) {
        event.stopPropagation()

        let input = event.currentTarget.querySelector('input[type="radio"]')

        if (input) {
            input.checked = true
            input.dispatchEvent(new Event('change', { bubbles: true }))
        }
    }

    async onChange(event) {
        event.stopPropagation()
        if (this.locked === true) {
            return false
        }

        this.locked = true
        let input = event.currentTarget

        if (!input) {
            return false
        }

        let statusName = input.getAttribute('name')
        let statusValue = null

        if (input instanceof HTMLInputElement && input.type === 'checkbox') {
            statusValue = Number(input.checked)
        } else if (input instanceof HTMLInputElement && input.type === 'radio') {
            this.icon.className = input.parentElement.querySelector('i').className
            statusValue = input.value
        }

        const response = await fetch(window.Rozier.routes.nodesStatusesAjax, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: new URLSearchParams({
                _token: window.Rozier.ajaxToken,
                _action: 'nodeChangeStatus',
                nodeId: parseInt(input.attr('data-node-id')),
                statusName: statusName,
                statusValue: statusValue,
            }),
        })
        if (!response.ok) {
            const data = await response.json()
            window.UIkit.notify({
                message: data.error_message,
                status: 'danger',
                timeout: 3000,
                pos: 'top-center',
            })
        } else {
            await window.Rozier.refreshMainNodeTree()
            await window.Rozier.getMessages()
        }
        this.locked = false
    }
}
