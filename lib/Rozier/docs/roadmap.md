# Roadmap

This file tracks current work on the Rozier UI.

## In progress

- Full visual redesign of the backoffice UI.
- Migrate the entire frontend codebase to TypeScript.
- Reduce or eliminate global variables and event listeners on `window`.
- Prefer native `CustomElements` to attach JavaScript logic to the DOM.
- Remove `Rozier.js` and replace its responsibilities with TypeScript modules
  and native `CustomElements`.
- Remove `Lazyload.js` and replace with native lazy-loading (`loading="lazy"`),
  `IntersectionObserver`, or small TypeScript utilities when needed.
- Remove UIkit and replace its UI components and styles with the new design
  system and native patterns.
- Remove jQuery and replace usages with DOM APIs and TypeScript utilities.
- Remove Vue.

## Principles while we modernize

- Keep changes incremental and easy to review.
- Avoid large rewrites without a clear migration path.
- Preserve existing behavior unless explicitly updated by design.
