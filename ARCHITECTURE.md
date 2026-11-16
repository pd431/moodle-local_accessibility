# Architecture: who owns what

One owner per rule. A change goes in the owner; a new rule gets an owner and a guard test before it gets a caller.

## Colour schemes (styles.css section 7, classes/colour/)

| Concern | Owner | Guard test |
|---|---|---|
| Scheme colours, tokens and their contrast floors | `classes/colour/scheme.php` | `tests/colour/scheme_test.php` |
| Mark colour for states (`--a11y-mark`, 3:1 on every shade) | `classes/colour/scheme.php`, `mark()` | `tests/colour/scheme_test.php::test_mark_meets_floor` |
| Status mark colours (`--a11y-ok-mark/-warn-mark/-bad-mark`, 3:1 on every shade), by scheme mode | `classes/colour/scheme.php`, `status_colours()` | `scheme_test::test_status_colours_meet_floors` |
| State marks: status cells, changed, chosen/current, disabled, read only, alerts, status text, progress, hidden, keyboard focus (the only place a state class is named) | `styles.css` s7a | `styles_test::test_state_marks_*`, `test_state_marks_follow_section_7`, `test_state_classes_named_once`, `test_nothing_after_state_marks_undoes_them`; `tests/behat/state_marks.feature` |
| Contrast maths (WCAG ratio, APCA) | `classes/colour/contrast.php` | `tests/colour/contrast_test.php` |
| Scheme variables onto Bootstrap variables | `styles.css` s7, `html[data-a11y-colour]` | `styles_test::test_new_variables_and_values` |
| Links by purpose (control anchors, link anchors, link-styled buttons) | `styles.css` s7, the `--la-anchor` rules | `styles_test::test_links_by_purpose` |
| Surfaces the theme hard-codes (cards, tables, gradebook, grading table, quiz editing) | `styles.css` s7, the mop-up rule | `styles_test::test_surface_mop_up`, `test_legacy_tables`, `test_page_specific_tables` |
| Rows and cells that change on hover | `styles.css` s7, the hover-surface rule | `styles_test::test_legacy_tables` |
| Small panels and chips (alerts, initials, badges) | `styles.css` s7, the second-surface rule | `styles_test::test_small_panels` |
| Utility surfaces core's dark mode repaints with more weight | `styles.css` s7, the weighted utility rule | `styles_test::test_weighted_utilities` |
| Text-entry fields and input-group add-ons | `styles.css` s7, the fields rule | `styles_test::test_fields` |
| Theme-coloured buttons | `styles.css` s7, the controls rules | `styles_test::test_theme_coloured_controls` |
| Hard-coded theme text colours | `styles.css` s7, the text-colour rule | `styles_test::test_hardcoded_text_colours` |
| Highlighted search terms | `styles.css` s7, the highlight rule | `styles_test::test_search_highlight` |
| Border, focus and selection tokens (the active token is the mark colour, s7a) | `styles.css` s7 and s11a | `styles_test::test_scheme_token_rules` |
| Focus for schemes without a focus token | `styles.css` s7a | `styles_test::test_focus_without_token` |
| Activity icons and other monochrome image icons | `styles.css` s7, the icon filter rules | `styles_test::test_activity_icon_filters`, `test_image_icons` |
| The text editor's toolbar | `styles.css` s7, the editor rule | `styles_test::test_editor_toolbar` |
| Navbar, popovers, messaging (issue #4) | `styles.css` s7 | `styles_test::test_issue4_rules` |

## Everything else

| Concern | Owner | Guard test |
|---|---|---|
| Preferences, cookie for guests, html attributes | `classes/preferences.php` | `tests/preferences_test.php` |
| Features and their choices | `classes/feature/` | `tests/feature/` |
| Profiles | `classes/profiles.php` | `tests/profiles_test.php` |
| Core dark mode bridge | `classes/colour_mode.php` | `tests/colour_mode_test.php` |
| Panel, launcher, read bar | `templates/`, `amd/src/`, `styles.css` s13 to s15 | `tests/behat/panel*.feature`, `styles_test` |

`styles.css` is written by hand. The reasons for each rule are in the comment above it.
