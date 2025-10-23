export type IconArgs = {
    className: string
    prefix?: string
    name?: string
    color?: string
    fontSize?: string
}

function getClassName(args: IconArgs) {
    if (args.className) return args.className
    if (args.prefix && args.name) return `rz-icon-${args.prefix}--${args.name}`
    return ''
}

export function iconRenderer(args: IconArgs) {
    const iconNode = document.createElement('span')
    if (args.color) iconNode.style.color = args.color
    if (args.fontSize) iconNode.style.fontSize = args.fontSize
    iconNode.className = getClassName(args)

    return iconNode
}

export function iconItemRenderer(args: IconArgs) {
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
      box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 3px 0px;
      border: 1px solid rgba(38, 85, 115, 0.15);
      overflow: hidden;
      padding: 0.6rem;
      align-items: center;
    `
    const iconNode = iconRenderer(args)

    const label = document.createElement('span')
    label.innerText = args.name || ''

    const nameNode = document.createElement('code')
    nameNode.innerText = args.className
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
