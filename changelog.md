# Changelog

## [3.1.2] - 2026-11-05

### Changed
- `styles.css` section 7: the theme-coloured controls list (both rules) adds `.btn:not(.btn-primary, .btn-success,
  .btn-danger, .btn-warning, .btn-info, .btn-dark, .btn-link, [class*="btn-outline-"])`; a new rule gives
  `html[data-a11y-colour] body .popover-region-toggle` `color: var(--a11y-text)`.
- Behat: vscode_scheme.feature scenario "Plain buttons and the notification toggle take the scheme".
- `styles.css` section 7: `.badge` joins the border-token surface list; `html[data-a11y-tokens~="border"] body
  .section .activity` sets `border-top: 0` (Boost's 1px `.activity` separator).
- Behat: vscode_scheme.feature scenario "Boxed activities have no separator line and a badge has an edge".

### Removed
- `.github/workflows/camp-release.yml`, `.github/workflows/moodle-release.yml` and `.camp/` (listing manifest and
  screenshots); the `.camp` export-ignore rule. `tag-on-merge.yml` loses its "Start the publish workflows" step and
  `actions: write`. GitHub Actions now runs Moodle Plugin CI and Tag release only.

## [3.1.1] - 2026-11-04

### Changed
- `styles.css` section 7: `.courseindex-link` joins the `a:is(...)` part of the link-purpose control list, for the
  course link in `#courseindexdrawerheading` (Moodle 5.2+), which sits outside the course index landmark.
- `styles.css` section 7: the active token rule and the focus-and-active offset rule add `.mds-nav-pill--selected`
  and `.mds-nav-pill.active`; `html[data-a11y-tokens~="active"] body :where(.mds-nav-pill__indicator,
  .mds-nav-pill.active::before)` is `display: none`; `html[data-a11y-tokens~="focus"] body
  .mds-nav-pill:where(:hover, :active)` has a transparent background.
- Behat: `the computed "X" of "Y" should contain "Z"` and `I give "X" keyboard focus` steps; a failed computed-style
  check now lists the element's ancestors with their `--la-anchor`; navigation selectors hold on 4.5, 5.2 and 5.3,
  with the tab box scenario on 5.2 or lower and a pill scenario on 5.3 or higher.

### Added
- `.github/workflows/tag-on-merge.yml`: on `workflow_run` of Moodle Plugin CI on main (success only) or
  `workflow_dispatch` (optional `sha`), reads `$plugin->release` from version.php, creates the annotated tag
  `v<release>` with the release's CHANGES.md section when it does not exist, and dispatches `moodle-release.yml` and
  `camp-release.yml` for it. `permissions: contents: write, actions: write`.

## [3.1.0] - 2026-11-03

### Changed
- Links by purpose under a colour scheme (`styles.css` section 7). The control list is stated once as the
  `--la-anchor` / `--la-anchor-line` custom properties on `nav`, `[role=navigation|menu|menubar|tablist|tree|toolbar]`,
  `h1`..`h6`, `thead th`, anchors with `role=button|tab|menuitem` or `.dropdown-item`, `.page-link`, `.nav-link`,
  `.badge`, and `a:has(> h1..h6)`; the single anchor rule paints `var(--la-anchor, var(--a11y-link))` and
  `var(--la-anchor-line, underline)`. Buttons and the active page or menu item stay excluded as before.

### Changed
- `styles.css`: the active token rule adds `background-color`, `border-color: transparent` and `color: var(--a11y-text)`;
  the focus token rule is `2px dashed` and also matches `:where(a, button, input, select, textarea, [role=button],
  [role=tab], [role=menuitem]):hover`; `.nav-tabs .nav-link` leaves the border token list and three rules give
  `.nav-tabs` a 1px `--a11y-border` bottom line, each `.nav-link` a 1px `--a11y-border` border with a transparent
  background, and `.nav-item + .nav-item .nav-link` a transparent left edge; after the active rule,
  `html[data-a11y-tokens~="border"][data-a11y-tokens~="active"] body .nav-tabs .nav-link.active` sets
  `border-color: var(--a11y-active)` and `box-shadow: inset 0 0 0 1px var(--a11y-active)`.
- `styles.css` section 11a: the focus token ring has `outline-offset: -2px`; the `ring` and `thick` variants set
  `outline-offset` to `-3px` and `-5px` and `box-shadow: none`; a fourth rule, keyed on the focus and active tokens
  together, gives the active rule's items `outline-offset: -4px` on `:focus-visible` and `:hover`.
- `styles.css` section 7: `.activity-header` joins the surface mop-up list, and `html[data-a11y-colour] body
  :where(.badge, .activityiconcontainer)` sets `mix-blend-mode: normal`.

### Added
- Scheme model: optional `active` colour (`scheme::fixed()` refuses one under 3:1 on any shade), token `active`,
  `--a11y-active`; vscodehc sets `#f38518`. `styles.css` section 7: `html[data-a11y-tokens~="active"]` draws an inset
  2px box-shadow on `.nav-link.active`, `.nav-link[aria-current]`, `.courseindex-item.pageitem`,
  `.courseindex-item.active`, `.dropdown-item[aria-current]` and `.page-item.active .page-link`.
- `colour\purpose`: Boost 4.5's six activity purpose colours and `colours($ramp, $text)`, each moved in OKLCH
  lightness with `adjust::towards()` until `contrast::apca_worst()` reaches `purpose::TARGET` (Lc 75) on page,
  surface1 and surface2, falling back to the text colour. `contrast::apca()` is SAPC-4 0.0.98G. `scheme::icon_colours()`
  and `--a11y-icon-<purpose>` custom properties; the panel template carries six `<filter id="la-icon-<purpose>">`
  (feFlood of that property, composited into the icon's alpha) and `styles.css` section 7 points each
  `.activityiconcontainer.<purpose>` icon at its filter with Boost's own selector shape.
- Colour scheme "Dark high contrast (VS Code)" (`vscodehc`): Visual Studio Code's Dark High Contrast palette. Black
  surfaces (floating widgets #0c141f, hover #111111) told apart by the #6fc3df contrast border, white text, #8bcaff (textLink #21a6ff lifted to APCA Lc 70)
  links, an #f38518 focus ring and #f3f518 on black selection. Built with `scheme::fixed()`, so the colours are kept
  exactly; the preset passes 7:1 on every shade and never sets `data-a11y-lowcontrast`.
- Scheme model: optional `border`, `focus`, `selection` and `selectiontext` colours, `scheme::fixed()` for presets
  designed as a whole, and `scheme::tokens()`. A scheme that declares any of them gets `data-a11y-tokens` on the
  html element (space-separated: `border focus selection`) and the matching `--a11y-*` custom properties.
- styles.css: a 1px border in `--a11y-border` on surfaces, fields, table cells and controls under the border token;
  a focus outline in `--a11y-focus` under the focus token (2px, or the Focus ring tile's 3px and 5px with the halo in
  the page colour); `::selection` under the selection token. `--bs-border-color` and the panel's edges take
  `--a11y-border` when present and fall back to `--a11y-text` as before.

## [3.0.7] - 2026-10-29

### Fixed
- Images, Dimmed and Hidden, left photos drawn as an inline `background-image` at full strength, such as format_tiles
  photo tiles and core's Dashboard course cards (#3); the rules matched only `img`, `picture` and `svg[role="img"]`.
  Elements whose inline style sets a background with a `url(...)` now lose the image for "hide"; for "dim" they get an
  inset `box-shadow` veil of the page colour at 60% (the same result as opacity 0.4 over the page), so the text on
  them keeps its contrast. The veil lifts on hover and focus. Background images set in a stylesheet are not covered.

## [3.0.6] - 2026-10-28

### Fixed
- Authored text with a fixed inline font size (pt, px, pc, in, cm, mm, Q, as written by TinyMCE and tiny_fontsize) or
  a keyword size (small, x-large...) did not follow the text size, which only scales the root font size (#2). While a
  size other than the default is chosen, the new `local_accessibility/inlinesize` module rewrites such sizes to
  `calc(<value> * var(--a11y-size, 100) / 100)` (keywords to rem), including content added to the page later. Fixed
  inline line heights scale the same way; inside authored text (`.no-overflow`, `.text_to_html`, `.qtext`), so do fixed
  inline heights, min-heights and max-heights, except on images, media, frames and maths. Editable regions and the
  toolbar itself are left alone. Legacy `<font size>` 1-7 gets rem sizes in `styles.css`.
- Colour schemes now override inline backgrounds written with the `background` shorthand, `<font color>` and `bgcolor`,
  and inline colours in authored text outside `#region-main` (for example HTML blocks), which could leave light text on
  a pale highlight or dark text on a dark page.

## [3.0.5] - 2026-10-24

### Changed
- The panel and read-aloud bar chrome (background, text, borders, accent and warning colours) use the Moodle Design
  System tokens (`--mds-*`, Moodle 5.3) first, then the Bootstrap variables, then the previous fixed colours, so the
  plugin's own UI follows Moodle 5.3's dark colour mode. Unchanged on Moodle 4.5-5.2, which define no `--mds-*` tokens.
- Borders inside the panel follow the active colour scheme's text colour when a scheme is chosen.

## [3.0.4] - 2026-10-16

### Changed
- The floating toolbar button is sized and placed in rem (2.75rem, 5rem from the bottom; 9rem when it moves off the
  focused element), so it scales with the user's text size; its icon is no longer capped by Boost's 30x24px icon
  limit. At 100% it is unchanged (44px, 80px).

### Fixed
- The floating toolbar button appeared above chat panels: its z-index is now 777 (was 1050).

## [3.0.3] - 2026-10-12

### Added
- camp registry publishing: the listing manifest `.camp/listing.yml` with screenshots, the `camp-release.yml`
  workflow, and `.gitattributes` export-ignore rules that keep development files out of release archives. The
  plugin's code and behaviour are unchanged.

### Changed
- Archives built from the git tag leave out `tests/`, `.github/`, `.camp/` and the git dotfiles.

## [3.0.2] - 2026-10-04

### Added
- The GPL-3.0 licence text as `LICENSE` in the plugin root (the licence itself is unchanged, GPL-3.0-or-later).

### Changed
- composer.json uses a caret constraint for `moodle/moodle` (`^4.5 || ^5.0`, was `>=4.5 <5.4`), so new Moodle 5.x
  releases are not excluded.
- CI tests `MOODLE_503_STABLE` (blocking) instead of moodle.git `main`, now that Moodle 5.3 is released.

## [3.0.1] - 2026-10-04

### Added
- composer.json, so the plugin can be installed with Composer as `adamjenkins/moodle-local_accessibility`
  (Moodle 4.5 to 5.3).

### Fixed
- The panel no longer gets paragraph spacing from the line height setting, which left a gap under the spacing
  options' sample lines.

## [3.0.0] - 2026-10-03

Rebuilt as a single plugin: the 11 accessibility widgets are built in, and existing users' settings are carried over
on upgrade. Requires Moodle 4.5 or later.

### Added
- A new panel: each tile shows its current setting in words; every option has an icon or preview and a label, in a
  drawer under the tile or a detail view, by keyboard or pointer. Keyboard shortcut Alt+A and a user menu entry.
  The panel follows the user's own text size, font, spacing and colours.
- Colour schemes: presets plus the user's own background, text and link colours, adjusted to 7:1 contrast unless
  the user keeps their exact colours.
- − and + steppers with a typed field and a Site default button for text size, line height, letter spacing, word
  spacing and line width, with no fixed list of values; the admin setting Limits of number settings (unlimited or
  non-negative).
- Fonts: Atkinson Hyperlegible, Lexend and Comic Neue (SIL OFL), OpenDyslexic Alta (Bitstream Vera licence and
  CC BY 3.0), device sans-serif, serif and monospace, three Japanese UD font stacks, and admin-uploaded fonts.
- Alignment, reading guide, stop motion, read aloud (browser voices), saturation, focus ring, large cursor, and
  link and image options.
- Profiles, site defaults and locks, validated site colour schemes, and first-visit device settings (reduced
  motion, more contrast).

### Changed
- Text size scales the whole page in proportion instead of flattening headings.
- Line height, letter spacing and word spacing are separate settings (previously the lineheight, letterspacing
  and fontkerning widgets).

### Removed
- The accessibility_* widget subplugins: they are uninstalled on upgrade when their folders are gone.

### Known limitations
- Line height, letter spacing and word spacing do not yet apply to headings.
