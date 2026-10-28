---
status: accepted
---

# Accessibility is an attention point, never a gate

Accessibility (contrast, visible focus, keyboard navigation, accessible labels) is checked on every Rozier change, but a finding never blocks a merge or the closing of a redesign zone. Cheap fixes land in the same change; the rest becomes a follow-up issue (`frontend` + `redesign-polish`). This keeps the one-active-zone redesign moving while accessibility debt stays visible in the tracker instead of stalling zones.

## Consequences

- If a delivery ever requires a formal RGAA gate on the backoffice, this ADR is superseded rather than worked around.
