# Roadiz

Terms shared by every package of the repository. Package-specific terms live in the glossaries listed in [GLOSSARY-MAP.md](./GLOSSARY-MAP.md).

## Stability

**Extension point**:
A mechanism through which a project built on Roadiz customises or extends it, and on which Roadiz promises stability: bundle configuration, dependency injection tags, events, the classes, interfaces and services the docs cite, API resources, Twig blocks of the backoffice entry points, Twig components and their options, design tokens. A mechanism is an extension point only once a page of `docs/` written for projects documents it; contributor pages (`docs/developer/contributing/`, `docs/adr/`, `docs/agents/`) and comments in the code do not count. Anything else is internal and may change in any release.
_Avoid_: Public API, hook

**Breaking change**:
A change that makes a project working on a tagged release fail, or behave differently from what the docs describe, without touching its code, through an extension point present in that release. A change to internal code, or to anything added since the last tag, is not a breaking change. How Roadiz avoids them is set by [ADR 0002](./docs/adr/0002-avoid-breaking-changes.md).
_Avoid_: BC break

**Deprecation**:
The minor release during which the old form of a changed extension point keeps working, warns that it will go, and names its replacement. It is removed in the next minor release.
_Avoid_: Legacy (when meaning deprecated)
