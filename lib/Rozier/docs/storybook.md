# Storybook

How stories are organised, named and written in `lib/Rozier/stories/`. Vocabulary
(story, foundation, component, pattern) lives in [`../GLOSSARY.md`](../GLOSSARY.md).

## Markup source

Stories build their markup with TypeScript renderers, never from Twig. When a PR
changes the markup of an `rz-*` root in a Twig template, it updates the matching
renderer in the same PR, and the other way round. Rationale:
[ADR 0004](adr/0004-typescript-renderers-for-story-markup.md).

## Tree

Three roots:

- `Foundations/`: colors, typography, icons, brand.
- `Components/<Group>/<Name>`: a building block reused across screens. Groups are
  functional, never redesign zones: `Actions`, `Form`, `Overlay`, `Navigation`,
  `Data display`, `Feedback`.
- `Patterns/<Name>`: a whole screen or a layout region (app layout, login,
  dashboard, header, aside, menu bar, live preview). An item of a pattern that is
  reused elsewhere is a component of its own (`Components/Navigation/HeaderNavItem`).

```
Foundations/   Colors, Typography/Text, Typography/FontFamily, Icons, Brand
Components/
  Actions/       Button, ButtonGroup, ActionsMenu, ColorSchemeSwitcher
  Form/          Input, Textarea, Select, Switch, ColorInput, Field, Fieldset,
                 Form, MarkdownEditor, Repeatable, FileUpload, ChildrenNodes/{Root,Item}
  Overlay/       Dialog, Drawer, Dropdown/{Root,Item}, Popover, Tooltip, Search
  Navigation/    HeaderNavItem, Breadcrumb, Tablist/{Root,Item}, Tree, Link, PageHeader
  Data display/  Table, Card, Badge, EntityThumbnail, NodeIcon, FiltersBar
  Feedback/      Message, Toast, Progress
Patterns/        App, Login, Dashboard, Header, Aside, MenuBar, LivePreview
```

A new story picks an existing group; adding a group is a deliberate change to this file.

## One file per `rz-*` root

- The file is named after its `rz-*` root in PascalCase: `rz-color-scheme-switcher`
  → `RzColorSchemeSwitcher.stories.ts`. No `RZ` prefix.
- The last title segment is the same name without `Rz`: `Components/Actions/ColorSchemeSwitcher`.
- A sub-component with its own root (dropdown item, tab, header item) gets its own
  file, titled `<Parent>/Item`; its parent is then titled `<Parent>/Root`. Without an
  item, the parent has no `Root` segment. Title segments are PascalCase.

## Stories and args

```ts
import type { Meta, StoryObj } from '@storybook/html-vite'
import { type RzBadgeOptions, rzBadgeRenderer } from '~/utils/component-renderer/rzBadge'

type Args = RzBadgeOptions

const meta: Meta<Args> = {
    title: 'Components/Data display/Badge',
    tags: ['autodocs'],
    render: (args) => rzBadgeRenderer(args),
    args: { label: 'My badge' },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const Published: Story = {
    args: { label: 'Published', color: 'success' },
}
```

- `Args` is the `Options` type exported by the renderer. A story never imports
  `Args` from another story file; shared shapes live in the renderer.
- `render` is declared once, in `meta`. Stories vary through `args` only, so the
  controls panel drives every story. A story that needs a different composition
  (a pattern, a demo with several instances) may declare its own `render`, still
  fed by `args`.
- The first export is `Default`; the others are named after the state or variant
  they show (`Disabled`, `WithIcon`, `IconOnly`).
- Imports from the app use `~/`, never `../app/`.
- Custom elements are registered globally by `.storybook/preview.ts`; stories do
  not import them.

## Renderers

- `app/utils/component-renderer/`: renderers also used by the runtime.
- `app/utils/storybook/renderer/`: renderers used by stories only.

One renderer per `rz-*` root, never duplicated. A renderer moves to
`component-renderer/` as soon as runtime code needs it.

## Verification

Before opening a PR that touches `stories/` or a renderer, run
`docker compose run --rm node pnpm build-storybook`: Storybook is not built in CI,
so a broken import only shows up there.
