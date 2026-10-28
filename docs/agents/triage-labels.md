# Triage Labels

The skills speak in terms of five canonical triage roles. This file maps those roles to the actual label strings used in this repo's issue tracker.

| Label in mattpocock/skills | Label in our tracker | Meaning                                  |
| -------------------------- | -------------------- | ---------------------------------------- |
| `needs-triage`             | `needs-triage`       | Maintainer needs to evaluate this issue  |
| `needs-info`               | `needs-info`         | Waiting on reporter for more information |
| `ready-for-agent`          | `ready-for-agent`    | Meets the [definition of ready](#definition-of-ready) |
| `ready-for-human`          | `ready-for-human`    | Requires human implementation            |
| `wontfix`                  | `wontfix`            | Will not be actioned                     |

When a skill mentions a role (e.g. "apply the AFK-ready triage label"), use the corresponding label string from this table.

## Decision flavour

`ready-for-human` covers every issue blocked on a team decision. When that decision belongs to the technical team (tooling, architecture, dependency or migration strategy) rather than product or design, also add `tech-decision`. It is not a state: the issue keeps exactly one state label.

## Definition of ready

An issue is **ready** (`ready-for-agent`) when an agent can finish it without asking anything. Check each item against the issue body and its comments; an issue missing one stays `needs-triage` or `needs-info`.

1. **Context**: the need and who it serves, in a few sentences.
2. **Acceptance criteria**: a checklist where each item is observable, so the agent can tell done from not-done.
3. **Out of scope**: what the change leaves untouched, when a neighbouring change is tempting.
4. **Location**: the package(s) and, when known, the files or screens involved.
5. **Verification**: how to prove the criteria hold (a PHPUnit test, a `make` target, or the Rozier feedback loop).

### Rozier issues

On top of the five items, filed with the **Rozier redesign** issue form (`.github/ISSUE_TEMPLATE/rozier-redesign.yml`):

- **Zone** named, using the vocabulary of `lib/Rozier/GLOSSARY.md`.
- **Figma** link to the matching section when one exists. A missing mockup keeps the issue ready: the implementer decides ([ADR 0002](../../lib/Rozier/docs/adr/0002-figma-is-a-starting-point.md)).
- **Verification** is the feedback loop of `lib/Rozier/docs/engineering-practices.md`.
- Accessibility findings never make an issue unready ([ADR 0003](../../lib/Rozier/docs/adr/0003-accessibility-never-blocks.md)).
