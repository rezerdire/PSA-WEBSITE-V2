# PSA Website UI Guidelines

**Purpose:** Project-level UI standard for every new feature or UI change in `PSA-WEBSITE-V2`.

**Audience:** Any AI assistant or developer changing the project interface.

**Authority:** This file is the project UI source of truth. Before creating a new feature, the implementer MUST read this file and inspect the nearest existing page/component patterns. The existing project UI takes precedence over a new visual style.

## Non-negotiable rules

1. **Use the existing project as the design basis.** Do not introduce a separate visual language, page shell, spacing system, color palette, typography system, or component convention when an existing project pattern covers the use case.
2. **Reuse before creating.** Search `resources/views/components`, `resources/views/layouts`, and the nearest feature pages before adding new markup. Extend an existing component only when the new behavior belongs to the same UI concept.
3. **Keep feature scope isolated.** Do not change global layout, navbar, footer, routes, models, database behavior, or unrelated pages to solve a feature-level UI issue unless the requested change is explicitly global and all affected pages are checked.
4. **Preserve behavior.** UI refactors MUST NOT change form submission, validation, Livewire actions, authorization, data loading, or route behavior unless that behavior change is explicitly requested.
5. **Verify the real surface.** Every UI change MUST be checked in a browser at desktop and mobile widths, including initial, loading, validation-error, success, empty, and long-content states that apply to the feature.

## Existing project foundation

### Global layout and chrome

- Use `resources/views/layouts/app.blade.php` for public pages.
- The layout already provides the global navbar, Livewire styles/scripts, Vite assets, and footer.
- The navbar is fixed, white, and approximately 64px high. New content MUST reserve space below it. Do not place headings or controls under the navbar after initial load or Livewire/Alpine updates.
- Keep the existing navbar and footer intact for ordinary public pages. Do not recreate site chrome inside a feature.
- Existing global references:
  - `resources/views/layouts/app.blade.php`
  - `resources/views/components/partials/navbar.blade.php`
  - `resources/views/components/partials/footer.blade.php`

### Containers and page shells

Choose the closest existing shell instead of inventing a new one:

- General public content: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`.
- Feature headers/content: commonly `max-w-6xl` with responsive padding.
- Form workflows: commonly a centered `max-w-4xl` container.
- Focused forms or compact task cards: `max-w-2xl` may be used when the content is intentionally narrow.
- Use a single page wrapper with intentional top spacing (`pt-24`, a shared feature header, or an equivalent existing pattern). Avoid nested `min-h-dvh`/`min-h-screen` wrappers that create unexplained empty space.
- Use `w-full` for the feature surface and center the content inside the chosen max-width container.
- Mobile layouts MUST remain within the viewport width. Check for horizontal overflow at common widths such as 390px and 412px.

### Page headers

Use one of the established header patterns:

- `resources/views/components/about-us-header.blade.php` for a full-width public feature header.
- `resources/views/components/mh-registry/page-header.blade.php` for a rounded blue-gradient feature header with metadata/progress.
- The compact page-title pattern used by `resources/views/pages/Gallery/index.blade.php` for content pages.

Header requirements:

- Use an eyebrow/section label, clear title, and optional supporting description.
- Keep heading hierarchy semantic: one page `h1`, then `h2`/`h3` for sections.
- Keep metadata inside the header or the feature card using the selected established pattern.
- Do not add a new icon-first title system unless the project does not have a suitable pattern and the reason is documented.

### Color palette and semantic states

- Primary public actions: Tailwind blue `600` with blue `700` hover where the nearest existing feature uses the modern public palette.
- Legacy registration/form workflows use the established navy `#000066`; follow the nearest workflow instead of mixing palettes arbitrarily.
- Body text: primarily `text-slate-900`, `text-slate-700`, `text-slate-600`, and `text-slate-500` according to hierarchy.
- Surfaces: `bg-white` cards over `bg-slate-50` section backgrounds; use `border-slate-200` or the nearest existing border token.
- Success/visited: emerald tokens (`emerald-50`/`100`/`600`/`700`).
- Pending/neutral: slate tokens (`slate-50`/`100`/`500`/`600`).
- Errors: red tokens with a visible border, surface, and `role="alert"`.
- Do not introduce a new brand color, gradient, status color, or arbitrary hex value when an existing token covers the meaning.

### Cards, spacing, and elevation

- Default feature/form card: white surface, light border, `rounded-2xl`, and `shadow-sm` or `shadow-md`.
- Use `rounded-3xl` only when matching an established surrounding feature pattern.
- Use `shadow-xl` sparingly; it is not the default project form elevation.
- Keep section padding consistent within a feature. Prefer the project’s `p-5`, `p-6`, `p-8`, `py-6`, or `py-8` scale over one-off values.
- Use borders and spacing to communicate sections before adding decorative effects.

### Typography

- Follow the nearest existing page/component typography instead of creating a new font stack.
- Page titles generally use `font-bold` or `font-extrabold`, tight tracking, and `text-slate-900` or white text on a feature header.
- Eyebrows and section labels use small uppercase text with controlled tracking, generally `tracking-wider`, `tracking-widest`, or the nearest existing value.
- Body copy generally uses `text-sm`, readable line height, and `text-slate-500`/`text-slate-600`.
- The project defines `--font-heading` for the loaded Cormorant Garamond heading font. Use an existing project class/pattern when applying it; do not introduce a second font system.
- Keep casing and terminology consistent with existing navigation and feature labels.

### Forms and controls

- Reuse `resources/views/components/form/label.blade.php` and `resources/views/components/form/input.blade.php` where their API fits.
- Shared input baseline: border, `rounded-lg`, compact `text-sm` scale, `py-2.5`, visible focus ring, and a stable error state.
- Every input MUST have a visible label or an intentional accessible name. Labels MUST point to the input with `for`/`id` when applicable.
- Use `inputmode` and an appropriate `type` for mobile keyboards. Do not use invalid HTML input types.
- Any `aria-describedby` reference MUST point to an element that exists in every relevant state. Error text MUST be associated with the field and exposed with `role="alert"` when appropriate.
- Validation errors MUST be inline, specific, readable, and visually connected to the affected control.
- Livewire forms MUST preserve loading/disabled states and MUST NOT allow accidental duplicate submission.
- Maintain at least a practical touch target on mobile even when following the compact project control scale.

### Buttons and links

- Primary action: blue project primary, compact `text-sm` scale, clear label, hover state, focus ring, and disabled/loading state.
- Secondary action: bordered white/slate control or the nearest established secondary pattern.
- Destructive action: use the project’s established warning/error treatment; do not style it as a normal primary action.
- Button labels MUST describe the action and use the same verb across equivalent routes.
- Links that navigate to an existing project route SHOULD use the existing navigation conventions (`wire:navigate` where the surrounding feature uses it).
- Do not use dark footer slate as an unexplained primary CTA.

### Responsive and interaction rules

- Design mobile-first and verify at minimum 390px wide plus a desktop viewport.
- Do not rely on hover for essential information or actions.
- Fixed navigation, Livewire morphs, Alpine transitions, pagination, validation messages, and expanded content MUST be checked for scroll position and overlap.
- Long names, locations, labels, errors, and list rows MUST wrap without breaking the layout.
- Do not add horizontal scrolling as a substitute for responsive layout unless the data genuinely requires it and the project already uses that pattern.
- Empty, loading, error, and success states MUST retain the same page shell and visual hierarchy as the initial state.

### Accessibility baseline

- Use semantic headings and landmarks.
- Every interactive control needs an accessible name.
- Preserve visible keyboard focus and sufficient contrast.
- Associate errors and help text with their controls.
- Do not hide important status changes from assistive technology; use live/status semantics where the interaction requires it.
- Check the mobile navbar and feature controls with keyboard and accessible-name inspection when they are affected.

## Required workflow for a new feature

1. **Map the feature surface.** Identify the route/page, Livewire component, layout, nearby components, and all existing callers.
2. **Select references.** Choose at least one existing feature with similar structure: Gallery, Workshop Registration, Annual Convention Registration, MH Registry, Membership, or another closest match.
3. **Write the UI plan.** State the selected page shell, header, container width, card treatment, form controls, action hierarchy, status colors, responsive behavior, and reusable components.
4. **Implement the smallest complete UI.** Reuse existing components and tokens. Add a new component only for a repeated or clearly bounded concept.
5. **Check every state.** Exercise initial, loading, validation error, success, empty, long-content, and pagination/filter states as applicable.
6. **Run browser verification.** Inspect desktop and mobile screenshots/measurements. Confirm no navbar overlap, horizontal overflow, broken focus state, dangling ARIA reference, or inconsistent action/status styling.
7. **Report scope.** List changed files, reused project patterns, behavior preserved, and the exact verification performed.

## AI instruction

When a prompt asks for a new UI feature in this project, the AI MUST treat this file as a required pre-implementation contract. The AI MUST use the existing project patterns as the basis, MUST reuse or extend existing UI components where practical, MUST keep unrelated UI unchanged, and MUST verify the rendered feature before claiming completion.

If a requested design conflicts with this standard, identify the conflict explicitly and ask only for the specific product decision that cannot be resolved from the repository. Do not silently create a second design system.

## Source references

The rules above are based on the current project implementation, including:

- `resources/views/layouts/app.blade.php`
- `resources/views/components/partials/navbar.blade.php`
- `resources/views/components/partials/footer.blade.php`
- `resources/views/components/about-us-header.blade.php`
- `resources/views/components/mh-registry/page-header.blade.php`
- `resources/views/components/event-registration/event-registration-layout.blade.php`
- `resources/views/components/event-registration/form-layout.blade.php`
- `resources/views/components/form/label.blade.php`
- `resources/views/components/form/input.blade.php`
- `resources/views/pages/Gallery/index.blade.php`
- `resources/views/pages/workshop/index.blade.php`
- `resources/views/pages/Event-Registration/events-registration.blade.php`
- `resources/css/app.css`
