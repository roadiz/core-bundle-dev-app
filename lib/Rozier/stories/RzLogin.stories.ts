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
    elements: Element[]
}

let counter = 0
function getFormElement() {
    counter++
    const form = document.createElement('form')
    ;[
        rzFormFieldRenderer({
            label: 'Username',
            required: true,
            input: {
                name: `username${counter}`,
                id: `username${counter}`,
                type: 'text',
                placeholder: 'Enter your username',
            },
        }),
        rzFormFieldRenderer({
            label: 'Password',
            required: true,
            help: '<a href="/rz-admin/login/request">Forgot password?</a>',
            input: {
                name: `password${counter}`,
                id: `password${counter}`,
                type: 'password',
                placeholder: '*******',
            },
        }),
        rzFormFieldRenderer({
            label: 'Keep me logged in',
            input: {
                className: 'rz-switch',
                name: `keepMeLoggedIn${counter}`,
                id: `keepMeLoggedIn${counter}`,
                type: 'checkbox',
            },
        }),
    ].forEach((el) => form.appendChild(el))

    return form
}

function getDefaultElements() {
    return [
        {
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
                    tag: 'a',
                    attributes: { href: '#' },
                    class: 'rz-button rz-button--secondary rz-login__item--end',
                    innerHTML: `
						<span class="rz-button__label">View website</span>
						<span class="rz-button__icon rz-icon-ri--arrow-right-up-line"></span>
					`,
                },
            ],
        },
        {
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
                    class: 'rz-login__group rz-login__group--row',
                    children: [
                        {
                            tag: 'a',
                            attributes: { href: '#' },
                            class: 'rz-button rz-button--secondary',
                            innerHTML: `
								<span class="rz-button__label">Log in by SSO</span>
								<span class="rz-button__icon rz-icon-ri--arrow-right-up-line"></span>
							`,
                        },
                        {
                            tag: 'a',
                            attributes: { href: '#' },
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
                    class: 'rz-login__group',
                    innerHTML: getFormElement().innerHTML,
                },
            ],
        },
        {
            tag: 'footer',
            class: 'rz-login__group rz-login__group--last',
            children: [
                {
                    tag: 'a',
                    class: 'rz-button rz-button--secondary',
                    attributes: { href: '#' },
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
    ]
}

const meta: Meta<Args> = {
    title: 'Components/Login',
    tags: ['autodocs'],
    args: {
        elements: getDefaultElements(),
    },
    parameters: {
        layout: 'fullscreen',
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

    args.elements.forEach((element) => {
        const el = elementRenderer(element)
        section.appendChild(el)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzLoginRenderer(args)
    },
    args: {
        elements: getDefaultElements(),
    },
}

export const NoFooter: Story = {
    render: (args) => {
        return rzLoginRenderer(args)
    },
    args: {
        elements: [
            {
                tag: 'header',
                class: 'rz-login__header',
                children: [
                    { tag: 'button', innerHTML: 'RZ', class: 'rz-brand' },
                    {
                        tag: 'span',
                        class: 'rz-badge',
                        innerHTML:
                            '<span class="rz-badge__label">V 3.1.2</span>',
                    },
                    {
                        tag: 'a',
                        attributes: { href: '#' },
                        class: 'rz-button rz-button--secondary rz-login__item--end',
                        innerHTML: `
						<span class="rz-button__label">View website</span>
						<span class="rz-button__icon rz-icon-ri--arrow-right-up-line"></span>
					`,
                    },
                ],
            },
            {
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
                        class: 'rz-login__group rz-login__group--row',
                        children: [
                            {
                                tag: 'a',
                                attributes: { href: '#' },
                                class: 'rz-button rz-button--secondary',
                                innerHTML: `
                                    <span class="rz-button__label">Log in by SSO</span>
                                    <span class="rz-button__icon rz-icon-ri--arrow-right-up-line"></span>
                                `,
                            },
                            {
                                tag: 'a',
                                attributes: { href: '#' },
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
                        class: 'rz-login__group',
                        innerHTML:
                            getFormElement().innerHTML +
                            `<div class="rz-login__group rz-login__group--last rz-login__group--full-width">
                                <a href="#" class="rz-button rz-button--secondary">
                                    <span class="rz-button__label">Ask help</span>
                                    <span class="rz-button__icon rz-icon-ri--arrow-right-s-line"></span>
                                </a>
                                <button class="rz-button rz-button--primary">
                                    <span class="rz-button__label">Log in with Magic Link</span>
                                    <span class="rz-button__icon rz-icon-ri--arrow-right-s-line"></span>
                                </button>
                            </div>`,
                    },
                ],
            },
        ],
    },
}
