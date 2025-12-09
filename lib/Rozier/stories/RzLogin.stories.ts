import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'

type Element = {
    [key: string]: unknown
    tag: string
    class?: string
    innerHTML?: string
    attributes?: Record<string, string>
    children?: Element[]
}

export type Args = {
    header: Element
    body: Element
    footer?: Element
}

const formElements = [
    rzFormFieldRenderer({
        label: 'Username',
        required: true,
        input: {
            name: 'username',
            id: 'username',
            type: 'text',
        },
    }),
    rzFormFieldRenderer({
        label: 'Password',
        required: true,
        help: '<a href="/rz-admin/login/request">Forgot password?</a>',
        input: {
            name: 'password',
            id: 'password',
            type: 'password',
        },
    }),
    rzFormFieldRenderer({
        label: 'Keep me logged in',
        input: {
            className: 'rz-switch',
            name: 'keepMeLoggedIn',
            id: 'keepMeLoggedIn',
            type: 'checkbox',
        },
    }),
]
function getFormElement() {
    const form = document.createElement('form')
    form.classList.add('rz-login__form')
    formElements.forEach((el) => form.appendChild(el))
    return form
}

const meta: Meta<Args> = {
    title: 'Components/Login',
    tags: ['autodocs'],
    args: {
        header: {
            tag: 'header',
            class: 'rz-login__header',
            children: [
                { tag: 'button', innerHTML: 'RZ', class: 'rz-brand' },
                {
                    tag: 'span',
                    class: 'rz-badge',
                    innerHTML: '<span class="rz-badge__label">V 3.1.2</span>',
                },
                {
                    tag: 'button',
                    class: 'rz-button rz-button--secondary rz-login__item--end',
                    innerHTML: `
						<span class="rz-button__label">View website</span>
						<span class="rz-button__icon rz-icon-ri--arrow-right-up-line"></span>
					`,
                },
            ],
        },
        body: {
            tag: 'div',
            class: 'rz-login__body',
            children: [
                {
                    tag: 'h1',
                    class: 'rz-login__title',
                    innerHTML: 'Backstage',
                },
                {
                    tag: 'div',
                    class: 'rz-login__group',
                    children: [
                        {
                            tag: 'button',
                            class: 'rz-button rz-button--secondary',
                            innerHTML: `
								<span class="rz-button__label">Log in by SSO</span>
								<span class="rz-button__icon rz-icon-ri--arrow-right-up-line"></span>
							`,
                        },
                        {
                            tag: 'button',
                            class: 'rz-button rz-button--secondary',
                            innerHTML: `
								<span class="rz-button__label">Log in with Magic Link</span>
								<span class="rz-button__icon rz-icon-ri--arrow-right-up-line"></span>
							`,
                        },
                    ],
                },
                {
                    tag: 'hr',
                    class: 'rz-login__divider',
                },
                {
                    tag: 'form',
                    class: 'rz-login__group rz-login__form',
                    innerHTML: getFormElement().innerHTML,
                },
            ],
        },
        footer: {
            tag: 'footer',
            class: 'rz-login__footer',
            children: [
                {
                    tag: 'button',
                    class: 'rz-button rz-button--secondary',
                    innerHTML: `
						<span class="rz-button__label">Ask help</span>
						<span class="rz-button__icon rz-icon-ri--arrow-right-s-line"></span>
					`,
                },
                {
                    tag: 'button',
                    class: 'rz-button rz-button--primary',
                    innerHTML: `
						<span class="rz-button__label">Log in with Magic Link</span>
						<span class="rz-button__icon rz-icon-ri--arrow-right-s-line"></span>
					`,
                },
            ],
        },
    },
}

export default meta
type Story = StoryObj<Args>

function elementRenderer(element: Element) {
    const el = document.createElement(element.tag)
    if (element.class) {
        el.className = element.class
    }
    if (element.innerHTML) {
        el.innerHTML = element.innerHTML
    }
    if (element.attributes) {
        for (const [attr, value] of Object.entries(element.attributes)) {
            el.setAttribute(attr, value)
        }
    }

    element.children?.forEach((child) => {
        const childElement = elementRenderer(child)
        el.appendChild(childElement)
    })

    return el
}

function rzLoginRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.classList.add('rz-login')

    const section = document.createElement('section')
    section.classList.add('rz-login__section')
    wrapper.appendChild(section)

    const header = elementRenderer(args.header)
    section.appendChild(header)

    const body = elementRenderer(args.body)
    section.appendChild(body)

    const footer = elementRenderer(args.footer)
    section.appendChild(footer)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzLoginRenderer(args)
    },
}
