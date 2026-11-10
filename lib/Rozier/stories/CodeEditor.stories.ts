import type { Meta, StoryObj } from '@storybook/html-vite'
import CodeMirror from 'codemirror'
import 'codemirror/mode/javascript/javascript'
import 'codemirror/mode/css/css'
import 'codemirror/mode/yaml/yaml'
import 'codemirror/addon/display/rulers'
// Loaded globally by `main.js` in the backoffice, not by the Storybook preview
import 'assets/css/vendor/codemirror.css'
import YamlEditor from '~/widgets/YamlEditor'
import CssEditor from '~/widgets/CssEditor'
import JsonEditor from '~/widgets/JsonEditor'

// The legacy widgets read CodeMirror from `window`, as set by `main.js`
Object.assign(window, { CodeMirror })

const EDITORS = {
    yaml: { attribute: 'data-rz-yamleditor', widget: YamlEditor },
    css: { attribute: 'data-rz-csseditor', widget: CssEditor },
    json: { attribute: 'data-rz-jsoneditor', widget: JsonEditor },
}

type Args = {
    language: keyof typeof EDITORS
    value: string
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
        const editor = EDITORS[args.language]
        const field = document.createElement('div')
        field.classList.add('rz-form-field')

        const textarea = document.createElement('textarea')
        textarea.setAttribute(editor.attribute, '')
        textarea.name = `code-editor-${args.language}`
        textarea.value = args.value
        field.appendChild(textarea)

        // Widgets need the textarea in the document to measure CodeMirror
        window.requestAnimationFrame(() => new editor.widget(textarea))

        return field
    },
    argTypes: {
        language: { control: 'select', options: Object.keys(EDITORS) },
    },
}

export default meta
type Story = StoryObj<Args>

export const Yaml: Story = {
    args: { language: 'yaml', value: YAML_SAMPLE },
}

export const Css: Story = {
    args: { language: 'css', value: CSS_SAMPLE },
}

export const Json: Story = {
    args: { language: 'json', value: JSON_SAMPLE },
}
