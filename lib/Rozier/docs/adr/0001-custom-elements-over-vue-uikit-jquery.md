---
status: accepted
---

# CustomElements over Vue, UIkit and jQuery

New and touched Rozier code is written in TypeScript and attaches its behaviour to the server-rendered Twig markup through native **CustomElements** (`rz-*`). The legacy stack is phased out incrementally rather than rewritten at once: Vue 2.7 and Vuex (end of life since late 2023), UIkit 2.x and the jQuery it depends on at runtime, the global `window.Rozier` singleton, and `Lazyload.js` (replaced by native `loading="lazy"`, `IntersectionObserver` or small TypeScript utilities).

## Consequences

- No new Vue component, UIkit behaviour (`data-uk-*`, `uk-*` classes) or jQuery call. Touching a legacy file is an opportunity to migrate it, not an obligation.
- jQuery and UIkit can only leave the bundle together, once their last live behaviours have a native replacement. Phase 1 of [`ui-redesign-todo.md`](../ui-redesign-todo.md) tracks that bounded scope.
- Rozier has no automated tests, so each removed behaviour needs a manual QA check.
