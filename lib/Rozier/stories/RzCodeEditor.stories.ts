import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    RZ_CODE_EDITOR_MODES,
    type RzCodeEditorOptions,
    rzCodeEditorRenderer,
} from '~/utils/storybook/renderer/rzCodeEditor'

type Args = RzCodeEditorOptions

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
        field.appendChild(rzCodeEditorRenderer(args))

        return field
    },
    args: {
        attributes: { name: 'code-editor' },
    },
    argTypes: {
        mode: { control: 'select', options: RZ_CODE_EDITOR_MODES },
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
    args: {
        mode: 'yaml',
        value: YAML_SAMPLE,
        attributes: { name: 'code-editor', disabled: true },
    },
}
