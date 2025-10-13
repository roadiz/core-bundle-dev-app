export type IconArgs = {
    prefix: string | null
    RzName: string | null
    RiName: string | null
    color: string
    fontSize: string
}

export function iconRenderer(args: IconArgs) {
    const iconName =
        args.prefix === 'rz'
            ? args.RzName
            : args.prefix === 'ri'
              ? args.RiName
              : ''

    const iconNode = document.createElement('span')
    iconNode.style.color = args.color
    iconNode.style.fontSize = args.fontSize
    const iconClassName = `rz-icon-${args.prefix}--${iconName}`
    iconNode.className = [iconClassName].join(' ')

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

    const iconName =
        args.prefix === 'rz'
            ? args.RzName
            : args.prefix === 'ri'
              ? args.RiName
              : ''

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
    label.innerText = iconName || ''
    label.style = `
    color: black;
    `

    const nameNode = document.createElement('code')
    nameNode.innerText = `rz-icon-${args.prefix}--${iconName}`
    nameNode.style = `
    color: black;
    grid-column: 1 / -1;
    user-select: all;
    `

    iconWrapper.appendChild(iconNode)
    item.appendChild(iconWrapper)
    item.appendChild(label)
    item.appendChild(nameNode)

    return item
}
