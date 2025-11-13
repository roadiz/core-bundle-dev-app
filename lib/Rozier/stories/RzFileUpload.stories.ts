import type { Meta, StoryObj } from '@storybook/html-vite'

export type Args = {}

const meta: Meta<Args> = {
    title: 'Components/FileUpload',
    tags: ['autodocs'],
    args: {},
}

export default meta
type Story = StoryObj<Args>

const circles = `<div class="circles-icons">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>
    <div class="circle circle-3"></div>
    <div class="circle circle-4"></div>
    <div class="circle circle-5"></div>
    <i class="uk-icon-rz-file"></i>
</div>`

function rzUploadFileRenderer(args: Args) {
    const el = document.createElement('div')
    el.classList.add('rz-dropzone', 'circle-5', 'dz-clickable')
    el.setAttribute('class', 'rz-dropzone circle-5')
    el.setAttribute('url', '/admin/documents/upload')

    const inner = document.createElement('div')
    inner.classList.add('dz-default', 'dz-message')
    el.appendChild(inner)

    const msg = document.createElement('span')
    msg.innerText = 'Drop files here to upload'
    inner.appendChild(msg)

    inner.insertAdjacentHTML('beforeend', circles)

    return el
}

export const Default: Story = {
    render: (args) => {
        return rzUploadFileRenderer(args)
    },
}
