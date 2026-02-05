# Product Options, Variants, and URL Sync

This document explains how a product’s purchasable options (Format, People, Sessions) are normalised from backend data, rendered in the product page, mapped to variants, and synchronised with the URL. It also covers how location selections in the main content area influence the buy box and URL.

Applies to:
- UI: `resources/js/Components/BuyBoxAdvanced.vue`
- Experience/overview panel: `resources/js/Components/WowxpExperience.vue`
- Page wrapper: `resources/js/Pages/Offering/Show.vue`

## Overview
- Products can come in two shapes: “legacy options” or “v3” data. Both render through `BuyBoxAdvanced`.
- Options are normalised into three user-facing dimensions where applicable:
  - `Format`: Online or In‑person
  - `People`: 1 Person, 2 Persons, 3+ Group
  - `Sessions`: a list of session labels
- A variant is the concrete purchasable combination (e.g., Online + 1 Person + 1 Session) with `id`, `price`, `compare`, `available`.
- The buy box shows each option as a row of “pills” (radio-like buttons). Selection updates price and URL, and resolves the best matching variant id.

## Data sources and normalisation
- Backend product object fields used:
  - `options`: array of option definitions (legacy or Shopify-style)
  - `variants`: array of purchasable variants with `id`, `options`, `price` (pence), `compare_at_price|compare`, `available`
  - `locations`: array of strings; used to infer `Format` when not present in options
  - Optional hints: `mode`, `price`, `price_min`, `price_max`

- Normalisation logic (high level):
  - If legacy option names contain “Location(s)”, “Person(s)”, “Session(s)”, they are mapped to `Format`, `People`, `Sessions` respectively.
  - If no explicit `Format` option exists, derive it from `locations` or `mode` (Online vs In‑person).
  - `People` values are canonicalised to: `1 Person`, `2 Persons`, `3+ Group`.
  - Each backend variant is mapped to a normalised variant by reading its tokens. If a variant lacks a `Format` token, fallback logic chooses an ordering (`Online` → first; `In‑person` → second) to keep state consistent.

References:
- Normalisation and variant shaping live in `resources/js/Components/BuyBoxAdvanced.vue` (functions `buildLegacyModel`, `buildV3Model`, `ensureVariantKV`, `variantMatchesSelection`).

## Rendering and selection (pills)
- UI is constructed programmatically in `buildOptionsInto(container)` within `BuyBoxAdvanced.vue`.
- Each pill is a `<button class="pill" role="radio" aria-checked="true|false">`.
- Clicking a pill:
  1. Updates `state.selected`
  2. Recomputes `state.variant`
  3. Updates price display
  4. Updates the URL with the latest selection and variant id
  5. For `Format`, also broadcasts a `wow:format-selected` event for the overview panel

## URL synchronisation
- The URL is the contract for current selection and is kept in sync on every change.
- Writer: `updateUrlFromSelection()` in `BuyBoxAdvanced.vue`.
- Rules:
  - `variant`: set to the resolved variant id. If no exact match is found, it is omitted.
  - `format`: included only after “format has been touched” (see below) so that the URL reflects an explicit or defaulted choice (Online or In‑person).
  - `people`: numeric value `1`, `2`, or `3` (for group). For group, the `group` param is also set with the group size.
  - `sessions`: exact session label when applicable.
  - `group`: included only when `3+ Group` is selected; value is the chosen group size.

- “Format touched” flag:
  - `window.__wow_formatTouched` gates whether `format=` is written to the URL. It is set to `true` when:
    - User clicks a Format pill
    - A location is clicked in the main Locations list (it mirrors the Format)
    - A `variant=` is present on load and is used to set selection
    - No selection params are present and defaults are auto‑selected on load

- Initialisation behaviour (page load):
  1. If `variant=` is present, selection is derived from that variant and pills are rebuilt to reflect it. The `format` flag is marked touched so `format=` is written next.
  2. Else, if `format=`/`people=`/`sessions=` are present, selection is derived from them.
  3. Else, first value of each option is auto‑selected and all relevant URL params are written immediately (format/people/sessions/variant, and `group` when applicable).

## Reading from URL (deep‑linking)
- Reader: `selectedFromUrl()` in `BuyBoxAdvanced.vue`.
- Behaviour:
  - If `variant=` exists, it takes priority and is converted back to a `selected` tuple (Format/People/Sessions) via `selectionForVariantId()`.
  - Otherwise, parses `format`, `people` (maps 1/2/3/group), and `sessions` to match the canonical values.

## Variant resolution
- `findVariant()` picks a variant that matches the current `state.selected`. If no exact match exists, the first available variant is used.
- `resolveRawVariantId()` tries to map the normalised selection back to a raw backend variant id for URL persistence.
- Fallbacks:
  - If variants don’t encode the `Format` dimension but the product offers both Online/In‑person, we use an index‑based fallback (first = Online, second = In‑person) to keep price/URL coherent.

## Locations integration (overview panel → buy box)
- The Locations section (in `WowxpExperience.vue`) emits two events when a location is clicked:
  - `wow:select-format` with `format: 'Online' | 'In-person'`
  - `wow:select-location` with `index: number` (for local highlighting only)
- The buy box listens and updates accordingly:
  - On `wow:select-format`: update `state.selected[Format]`, recompute variant, update URL, mark format touched, and refresh pills.
  - On `wow:select-location`: recompute variant and update URL (no direct mapping to a variant index; avoids desync).

## Group pricing and URL
- When `People = 3+ Group` is selected, the unit price is computed from either explicit group pricing metadata or inferred steps. The `group` URL param is included with the selected size.
- When not in group mode, the compare/strike price is shown if the current variant has a `compare` value larger than price.

## Examples
- A fully specified deep link:
  ```
  /therapies/6464-birth-chart-reading?format=Online&people=1&sessions=1&variant=10126
  ```
- A defaulted link (no params on initial load):
  - The app selects the first option of each dimension and writes:
  ```
  ?format=In-person&people=1&variant=12345
  ```
  (Actual values depend on the product’s options/variants.)

## Common pitfalls and notes
- If backend variants omit the `Format` token entirely, the fallback order (first=Online, second=In‑person) is applied where necessary. Prefer providing explicit `Format` in variant options when both Online and In‑person exist.
- URL `people` must be numeric (1/2/3). The label mapping is internal; the URL stays numeric for consistency.
- `sessions` must match one of the visible session labels after normalisation.
- If you add new option types, ensure they are either ignored or explicitly mapped; only `Format`, `People`, and `Sessions` are currently synced to the URL.

## Where to look in code
- `resources/js/Components/BuyBoxAdvanced.vue`
  - Normalisation: `buildLegacyModel`, `buildV3Model`
  - URL read: `selectedFromUrl`
  - URL write: `updateUrlFromSelection`
  - Variant resolve: `findVariant`, `resolveRawVariantId`, `ensureVariantKV`, `variantMatchesSelection`
  - UI (pills): `buildOptionsInto`
  - Integration events: listeners for `wow:select-format` and `wow:select-location`
- `resources/js/Components/WowxpExperience.vue`
  - Locations list, event emitters, and tab/section logic
- `resources/js/Pages/Offering/Show.vue`
  - Chooses `BuyBoxLegacy` vs `BuyBoxV3` (both wrap `BuyBoxAdvanced`)

---
For changes that ensure URL defaults and proper pill activation from `variant=` links, see recent updates to `BuyBoxAdvanced.vue` (initialisation and event handlers).
