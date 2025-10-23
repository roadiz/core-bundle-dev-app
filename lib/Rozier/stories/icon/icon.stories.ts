import type { Meta, StoryObj } from '@storybook/html-vite'
import { iconItemRenderer, iconRenderer, type IconArgs } from './iconItem'

type IconData = {
    folder: string
    className: string
    fileName: string
    path: string
    loader: () => Promise<string>
}

const allIconNames: string[] = []
const svgModules = import.meta.glob('../../app/assets/img/icons/*/*.svg', {
    import: 'default',
    eager: false,
})
const svgList = Object.keys(svgModules).reduce(
    (acc: Record<string, IconData[]>, path) => {
        const parts = path.split('/')
        const folder = parts[parts.length - 2]
        const fileName = parts[parts.length - 1].replace('.svg', '')

        if (!acc[folder]) {
            acc[folder] = []
        }

        const className = `rz-icon-${folder}--${fileName}`
        allIconNames.push(className)

        acc[folder].push({
            folder,
            className,
            fileName,
            path,
            loader: svgModules[path],
        })
        return acc
    },
    {},
)

const meta: Meta<IconArgs> = {
    title: 'Integration/Icon',
    argTypes: {
        fontSize: {
            control: 'text',
            description: 'A CSS unit font-size',
        },
    },
}

export default meta
type Story = StoryObj<IconArgs>

export const All: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')

        Object.values(svgList).forEach((icons) => {
            const collectionNode = document.createElement('div')
            collectionNode.style = `
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 42px;
                margin-inline: auto;
                max-width: 1000px;
                margin-block: 100px;
            `
            wrapper.appendChild(collectionNode)

            const collectionTitle = document.createElement('h3')
            collectionTitle.innerText = 'Collection: ' + icons[0].folder || ''
            collectionNode.appendChild(collectionTitle)

            icons.forEach((icon) => {
                const iconNode = iconItemRenderer({
                    className: icon.className,
                    prefix: icon.folder,
                    name: icon.fileName,
                    fontSize: args.fontSize,
                })
                collectionNode.appendChild(iconNode)
            })
        })

        return wrapper
    },
}

export const ClassName: Story = {
    render: (args) => {
        return iconRenderer(args)
    },
    args: {
        color: '#1E40AF',
        fontSize: '92px',
        className: allIconNames[0],
    },
    argTypes: {
        className: {
            options: allIconNames,
            control: { type: 'select' },
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export const FromSvg: Story = {
    render: (args) => {
        const node = document.createElement('div')
        node.style = `
            width: 100px;
            height: 100px;
        `
        const svgPath = args.svgPath || `ri/close-large-line`

        try {
            import(`../../app/assets/img/icons/${svgPath}`).then((module) => {
                console.log('module', module)
                node.style.background = `url("${module.default}")`
                node.style.backgroundSize = 'contain'
                node.style.backgroundRepeat = 'no-repeat'
            })
        } catch (error) {
            console.error('Error loading SVG:', error)
        }

        return node
    },
    args: {
        svgPath: 'ri/close-large-line.svg',
    },
    parameters: {
        layout: 'centered',
    },
}
