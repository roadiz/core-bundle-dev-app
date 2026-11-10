---
status: accepted
---

# Figma is a starting point, not a closed spec

The `Roadiz - V3.0` Figma file does not mock up every value, state or screen of the backoffice. Rather than blocking a ticket until a mockup exists, the implementer decides the gap in line with the existing `rz-*` components and design tokens, keeps going, and records the choice (with a screenshot) in the issue or PR so the team can revisit it. Only product decisions are escalated to the team.

## Consequences

- A Figma gap never moves an issue out of `ready-for-agent`; `ready-for-human` is reserved for product decisions.
- Reviewers judge consistency with the design system, not pixel parity with a mockup that does not exist.
