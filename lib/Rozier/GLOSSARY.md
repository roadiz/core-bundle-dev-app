# Rozier

The Roadiz backoffice UI: the screens editors use to manage nodes, documents, tags and users, and the redesign that brings them onto a new design system.

## Interface

**Zone**:
A functional area of the backoffice, grouping the screens and components serving one editing task. The redesign plans and closes work zone by zone.
_Avoid_: Module, section, page

The zones are: **Content editing** (node-source edit page), **Navigation** (tree, main menu, header), **Media library** (documents), **Tables** (listings and bulk actions), **Dialogs** (cross-cutting dialogs and overlays), **Explorer** (drawer picking related entities), **Tags and Folders**, **Custom forms**, **User management**. Work tied to no zone (themes, design tokens, technical foundations) is not a zone: it belongs to a **Phase**.

**Component**:
A reusable building block of the backoffice UI, named after its `rz-*` root and used across several screens; its markup, styles, behaviour and story share that name.
_Avoid_: Widget, macro, partial

**Custom element**:
A UI component of the new design system, a native web component named `rz-*`.
_Avoid_: Widget, Vue component, web component

**Redesigned component**:
A component that follows the new design system, whatever its implementation.

**Legacy component**:
A component still built on the pre-redesign UI (Vue, UIkit or jQuery), to be migrated or explicitly excluded.
_Avoid_: Old component, v1 component

**Design token**:
A named design value (color, spacing, type, radius) shared by every redesigned component and switched by the theme.
_Avoid_: CSS variable, magic value

**Theme**:
The light or dark appearance of the backoffice. A zone is not done until both themes are checked.
_Avoid_: Dark mode (when meaning both themes)

**Story**:
The Storybook entry documenting a foundation, a component or a pattern, and its states.

**Foundation**:
A base of the design system shown on its own rather than as a component: colors, typography, icons.
_Avoid_: Integration, base styles

**Layout**:
The skeleton a backoffice page is rendered into, around its own content: document head, header, menu bar, aside. The main layout is the one editing pages use; the login and the dashboard have their own.
_Avoid_: Base, master page

**Pattern**:
A whole screen or a layout region of the backoffice, documented as an assembly of components (login screen, dashboard, application layout, menu bar). A building block reused across several screens is a component, not a pattern.
_Avoid_: Integration, template, page

**Extension point**:
A mechanism through which a project built on Roadiz customises the backoffice, and on which Roadiz promises stability. A mechanism is an extension point only once it is documented for projects; anything else is internal.
_Avoid_: Hook, override (when meaning any template a project replaces)

## Redesign process

**Active zone**:
The single zone currently being redesigned. Ideas outside it go to the backlog.

**Pilot zone**:
The first active zone (Content editing), whose validated themes serve as the reference for cross-cutting contrast work.

**Done zone**:
A zone whose legacy components are migrated or excluded, whose themes are checked, which has been compared against its Figma section, whose stories are up to date and whose related issues are closed.
_Avoid_: Finished, complete (without the criteria)

**Phase**:
An ordered step of the redesign roadmap, grouping work that is not tied to a single zone (hygiene, cross-cutting contrast, technical foundations) or that sequences the zones.
_Avoid_: Batch, lot, sprint

**Closed phase**:
A phase whose every item is either done or explicitly excluded (moved to another phase or milestone, or dropped) with a written trace.
_Avoid_: Finished phase, done phase

**Polish**:
Work on a component or zone the redesign already shipped. Labelled `redesign-polish`.

**New scope**:
Work on a zone or task the redesign has not covered yet. Labelled `redesign-new-scope`.
