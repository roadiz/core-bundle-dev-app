import type { Meta, StoryObj } from '@storybook/html-vite'
// Loaded globally by `main.js` in the backoffice, not by the Storybook preview
import 'assets/css/vendor/codemirror.css'

const MODES = ['yaml', 'css', 'json'] as const

type Args = {
    mode: (typeof MODES)[number]
    value: string
    disabled: boolean
}

const YAML_SAMPLE = `# Site settings
---
title: Roadiz
enabled: true
max_items: 42
ratio: 1.5
defaults: &defaults
    locale: 'fr'
production:
    <<: *defaults
    hosts:
        - www.roadiz.io
        - "cdn.roadiz.io"
`

const CSS_SAMPLE = `/* Custom styles */
@media (min-width: 768px) {
    .page-header > h1,
    #main a:hover {
        display: block;
        margin: 0 auto 12px;
        color: #2050f5 !important;
        font-family: "Inter", sans-serif;
        width: calc(100% - var(--gutter));
    }
}
`

const JSON_SAMPLE = `{
    "title": "Roadiz",
    "enabled": true,
    "count": 42,
    "ratio": 1.5,
    "parent": null,
    "tags": ["cms", "symfony"]
}
`

const meta: Meta<Args> = {
    title: 'Components/Form/CodeEditor',
    tags: ['autodocs'],
    render: (args) => {
        const field = document.createElement('div')
        field.classList.add('rz-form-field')

        const editor = document.createElement('rz-code-editor')
        editor.classList.add('rz-code-editor')
        editor.setAttribute('mode', args.mode)

        const textarea = document.createElement('textarea')
        textarea.name = `code-editor-${args.mode}`
        textarea.value = args.value
        textarea.disabled = args.disabled

        editor.appendChild(textarea)
        field.appendChild(editor)

        return field
    },
    args: {
        disabled: false,
    },
    argTypes: {
        mode: { control: 'select', options: MODES },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    args: { mode: 'yaml', value: YAML_SAMPLE },
}

export const Css: Story = {
    args: { mode: 'css', value: CSS_SAMPLE },
}

export const Json: Story = {
    args: { mode: 'json', value: JSON_SAMPLE },
}

export const Disabled: Story = {
    args: { mode: 'yaml', value: YAML_SAMPLE, disabled: true },
}
