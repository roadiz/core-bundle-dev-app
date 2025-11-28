import type { Meta, StoryObj } from '@storybook/html-vite'

export type IconArgs = {
    className: string
    prefix?: string
    name?: string
    color?: string
    fontSize?: string
    svgUrl?: string
}

type IconData = {
    folder: string
    className: string
    fileName: string
    path: string
    loader: () => Promise<string>
}

// HELPERS
function getClassName(args: IconArgs) {
    if (args.className) return args.className
    if (args.prefix && args.name) return `rz-icon-${args.prefix}--${args.name}`
    return ''
}

function iconRenderer(args: IconArgs) {
    const iconNode = document.createElement('span')
    if (args.color) iconNode.style.color = args.color
    if (args.fontSize) iconNode.style.fontSize = args.fontSize
    iconNode.className = getClassName(args)

    return iconNode
}

function iconItemRenderer(args: IconArgs) {
    const item = document.createElement('div')
    item.style = `
      display: grid;
      align-items: center;
      gap: 12px;
      grid-template-columns: min-content 1fr;
    `

    const iconWrapper = document.createElement('div')
    iconWrapper.style = `
      display: flex;
      border-radius: 4px;
      box-shadow: var(--surface-overlay) 0px 1px 3px 0px;
      border: 1px solid rgba(38, 85, 115, 0.15);
      overflow: hidden;
      padding: 0.6rem;
      align-items: center;
    `
    const iconNode = iconRenderer(args)

    const label = document.createElement('span')
    label.innerText = args.name || getClassName(args)?.split('--').pop() || ''

    const nameNode = document.createElement('code')
    nameNode.innerText = getClassName(args)
    nameNode.style = `
      grid-column: 1 / -1;
      user-select: all;
    `

    iconWrapper.appendChild(iconNode)
    item.appendChild(iconWrapper)
    item.appendChild(label)
    item.appendChild(nameNode)

    return item
}

// Get all icon dynamically
const allIconNames: string[] = []
const svgModules = import.meta.glob('../app/assets/img/icons/*/*.svg')

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
            collectionTitle.innerText = `Collection: ${icons[0].folder || 'unknown'}`
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

export const FromClassName: Story = {
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

const allPath = Object.values(svgList).flatMap((icons) =>
    icons.map((icon) => icon.path),
)
export const FromSvgAssetPath: Story = {
    render: (args) => {
        const node = document.createElement('div')
        node.style = `
            width: 100px;
            height: 100px;
        `

        if (args.svgUrl) {
            try {
                import(args.svgUrl).then((module) => {
                    node.style.background = `url("${module.default}")`
                    node.style.backgroundSize = 'contain'
                    node.style.backgroundRepeat = 'no-repeat'
                })
            } catch (error) {
                console.error('Error loading SVG:', error)
            }
        }

        return node
    },
    argTypes: {
        svgUrl: {
            options: allPath,
            control: { type: 'select' },
        },
    },
    args: {
        svgUrl: allPath[0],
    },
    parameters: {
        layout: 'centered',
    },
}
