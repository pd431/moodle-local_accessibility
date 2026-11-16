# 3.1.5

States keep their mark under every colour scheme. A scheme gives a state and its neighbour the same colours, so the
current page, a ticked row, a disabled button or a failing grade looked like the items around it. Found by checking
97 states on Moodle 4.5, 5.2 and 5.3 under every scheme, against upstream 3.0.9 and under core Boost and Boost Union.
Revised after review on 16 November: no dashed, dotted or double lines; status shows in colour again, as a dot before
status text and as solid borders and markers.

- Every scheme has a mark colour: its link colour, or its text colour where the link is under 3:1 on a surface. VS Code
  dark high contrast keeps its orange.
- Every scheme has status mark colours for ok, warning and bad, 3:1 on every surface, from two fixed sets (the 3.1.4
  colours for dark schemes, darker ones of the same tone for light schemes), with lightness moved where a value falls
  short.
- Chosen and current items (the current page, tab, pill, breadcrumb (also the plain last item Moodle shows), menu and
  list item, a pressed toggle or editor button, a ticked or active row, today in the calendar) are boxed in the mark
  colour on the hover surface. Before, only VS Code dark high contrast boxed them.
- Status cells (a submission's status, a late or early submission, a coloured table cell or row) drop the theme's fill
  and have a solid 2px border in the status mark colour. A changed quick grade or an overridden grade has the same
  border in the warning colour. A status cell in a ticked row shows both marks.
- Alerts show their type as a solid marker 1em wide at the leading edge, in the type's colour (info in the link
  colour). Badges and pills are painted the same way: the second surface, the scheme's text colour, a 1px edge, and for a
  success, warning, danger or info badge a marker 0.75em wide. Primary, light and dark badges keep the theme's colours.
  Coloured buttons are not painted yet.
- Status text (success, warning and danger text, passing and failing grades, error messages) keeps the scheme's text
  colour, with a dot before it in the status mark colour. An icon that carries a status takes the mark colour instead.
  A field with an error takes an edge in the danger colour.
- Disabled menu items, buttons, tabs, page links, editor buttons and fields are faded to 0.6. Read-only fields keep
  full contrast and take a solid 2px edge in the text colour.
- A progress bar's fill is in the mark colour, on a track with an edge.
- Hidden items, hidden courses and suspended users are set in italic, at full contrast.
- Keyboard focus under a scheme without its own focus colour is drawn in the mark colour, and moves inside a current
  item's box under every scheme.
- Alert edges no longer use fixed hex colours.
- Tests: the Behat step "should look different from" uses a 3:1 rule (it passed on any difference before), counts an
  edge or underline in another style or weight, or a shadow of another shape, as another mark, and counts Bootstrap
  5's table fills as background. A drawn pseudo-element (a status dot) counts as a mark. A new step, "should
  differ in hue from", records one status against another, told apart by hue alone. A new feature, state_marks, covers each state. active_item_box expects the box under every scheme;
  link_purpose and vscode_scheme expect VS Code's link colour since 3.1.4 (#a3d5ff).

# 3.1.4

Contrast of standard Moodle components under a colour scheme, found by measuring 70 pages on Moodle 4.5 and 5.3 at
rest, hovered, focused and with menus open.

- Tables that use Moodle's own table style without Bootstrap's (most admin tables, the participants list, the user
  list) take the scheme on Moodle 4.5: before, their text stayed dark on a dark page and every other row stayed white.
  Hovered rows take the scheme's hover surface.
- Pagination, the gradebook (grader report, user report, grade setup), the assignment grading table and the quiz
  editing page take the scheme. Before, their cells stayed white or pale grey under the scheme's light text.
- Alerts and other small pale panels (the plugin update panel, status cells, user initials, grey badges) take the
  scheme's second surface. An alert keeps an edge in its type's colour, since its fill no longer shows its type.
- Text the theme colours itself takes the scheme's text colour: `.text-dark`, plain text, headers and buttons in menus,
  activity dates, grade category toggles, "Not marked", question names on the quiz editing page, and the grey
  component names on Plugins overview.
- A search term marked in results (site administration search, course search) takes the text colour on the hover
  surface, underlined. Before, it kept Boost's teal, which is APCA Lc 30 on a black page.
- Link-styled buttons (Show more, Expand all, move and info icons) take the link colour. Before, they kept the theme's
  brand colour, and turned black when hovered.
- A site's own forced link colours (an `!important` rule in a theme's custom SCSS) no longer beat the scheme.
- Keyboard focus shows under every scheme. A scheme without its own focus colour draws a solid ring in its text colour
  inside the control, and a focused link loses the theme's pale fill there too. Before, High contrast relied on the
  theme's ring, which is mixed from the brand colour and can vanish on a dark page. The Focus ring tile's Ring and
  Extra thick are unchanged.
- Fields and selects have an edge in the scheme's border or text colour, and an input group's add-on takes the field
  colour.
- White and light utility panels follow the scheme on Moodle 5.3, where core's dark mode repainted them dark grey.
- The text editor's menu bar, toolbar and status bar take the scheme on Moodle 4.5. The editing area keeps the
  author's own colours.
- Dark image icons (the icon beside each plugin on Plugins overview, gradebook item icons, the group mode icon) are
  lightened under a dark scheme.
- Dark high contrast (VS Code): links are lighter, #a3d5ff, APCA Lc 77 on every surface of the scheme (was #8bcaff, Lc
  70). Lc 75 is what link text at Moodle's paragraph size needs.
- An ARCHITECTURE.md table says which rule owns which concern and which test guards it.

# 3.1.3

- Merges upstream 3.0.8 (Adam Jenkins): the panel stays open while you try settings, Site default comes first in
  every stepper, Line width opens under its tile, profiles replace your settings, Links "Off" is "Standard", the
  large cursor shows over every control but text entry, and under a colour scheme the navigation bar, menus,
  notification and messaging panels, conversations and placeholders all take the scheme's colours, including on
  Boost Union. See 3.0.8 below.
- Dark high contrast (VS Code) follows the same hover rule as every other scheme: a hovered menu item, course tab or
  navigation pill takes the scheme's hover surface, and the dashed orange ring now marks keyboard focus only. The
  current item keeps its solid orange box, drawn on that surface rather than on bare black, and keeps the menu's
  underline in the text colour.

# 3.1.2

- Under a colour scheme, plain buttons (the course index and block drawer toggles, the user menu toggle, the
  Completion dropdown on an activity) take the scheme's text colour, surface and border like secondary buttons.
  Before, they kept Boost's dark text, and the Completion dropdown its white background, so on a dark scheme their
  icons were black on black. The notification bell in the top bar takes the text colour too; it is not a link, so the
  link rule never reached it and it stayed black at 60% while the messages icon beside it was white.
- Dark high contrast (VS Code), and any scheme with a border colour: no separator line between activities, since
  each activity already has its box; the line only doubled the top edge. Badges, such as the completion conditions
  in an activity's header on Moodle 4.5 ("View") and the badges on activity cards, get the same 1px edge, so a pill
  no longer reads as bare text on the surface. (Moodle 5.2 and later list the completion conditions as text.)

# 3.1.1

- Dark high contrast (VS Code) on Moodle 5.3: the top navigation and the course menu are now design-system pills,
  and the current pill gets the same solid orange box, with its fill and dot removed; hovered and pressed pills lose
  their fill and show the dashed ring. Pills are not boxed in cyan: they are not tabs, and the bar keeps its line.
- A Tag release workflow tags main with the plugin's release after a green CI run, or on demand from the Actions
  tab, and starts the publish workflows. Tags are never moved: a merge that keeps the release makes no tag.
- Under a colour scheme on Moodle 5.2 and 5.3, the course name at the top of the course index drawer takes the
  text colour like the index below it. Before, it was painted in the link colour, since it sits above the index's
  navigation landmark.

# 3.1.0

- New colour scheme, Dark high contrast (VS Code): the palette of Visual Studio Code's Dark High Contrast theme. Every
  surface is black and told apart by a cyan border, text is white, links are VS Code's blue, keyboard focus shows an
  orange ring whatever the Focus ring tile says (Ring and Extra thick keep their thickness), and selected text is
  yellow on black. Every text and link pair is at or above 7:1.
- Dark high contrast (VS Code): links are a lighter blue, #8bcaff. VS Code's own #21a6ff passes WCAG 2 at 7:1 but
  reaches only APCA Lc 51 on black; the new blue is the same hue at Lc 70 (10.6:1). Activity names in course content
  take the text colour, like the course index: the card and icon already say they are clickable.
- Under a colour scheme, links are coloured by purpose. An anchor in a navigation landmark or a menu, tab, tree or
  toolbar widget, a tab, menu item, dropdown item, page link, nav link or badge, a sort link in a table head, or a
  block link in or around a heading (section titles, course cards) takes the text colour with no forced underline.
  Every other anchor, on every page, keeps the link colour and underline. Before, every anchor but buttons was
  painted in the link colour, so the navbar, the course index and the tabs read as a wall of links.
- Under a colour scheme, activity icons keep their purpose colour (Boost's six) but are lifted or lowered in
  lightness, hue kept, until they reach APCA Lc 75 against the page and card surfaces. Before, Boost's own colours
  stayed as they are, which on a black page is Lc 13 to 43 (brown and violet are the worst). APCA is used for the
  icons only, because WCAG 2 over-rates saturated colour on a dark page; text and links keep their 7:1 ratios.
- Dark high contrast (VS Code) boxes the current item in orange, as VS Code's contrastActiveBorder does: the current
  page in the top nav, the current course tab, the current activity or section in the course index, the current
  item in a dropdown and the current page in pagination. The box is drawn inside the item, so the keyboard focus ring
  still shows around it.
- Dark high contrast (VS Code): one mark per state. The current item has its solid orange box and nothing else (no
  fill, no theme underline); keyboard focus and the pointer over a link or control show a dashed orange ring outside
  the item, so a current item under the pointer reads as solid inside, dashed outside; the course tabs have one cyan
  line under the bar. The Focus ring tile's Ring and Extra thick keep their thickness, dashed.
- Dark high contrast (VS Code): each course tab is boxed in cyan, neighbours sharing one edge and the tab bottoms
  sitting on the bar line, so no line is ever doubled; the current tab is boxed in orange alone, with no cyan around
  it.
- Dark high contrast (VS Code): the dashed focus and hover ring is drawn inside the item's edge instead of around it,
  on the item's own surface. On a current item it sits inside the solid orange box. The Focus ring tile's Ring and
  Extra thick go inside too and drop their page-colour halo, which only framed a ring drawn outside.
- Under a colour scheme, the activity header (the strip under the tabs on a module page that holds the completion
  conditions, dates and description) takes the surface colour. Before, it kept Boost's light grey under every scheme,
  so on a dark scheme it stood out and its white text was unreadable. The completion badges in it, and the badge and
  icon of a hidden or hovered activity card, no longer multiply into the surface behind them, which on a dark surface
  turned their white text and icons black.
- Colour schemes can now declare a border, a focus, a selection and an active colour. The page lists the ones a scheme declares
  in a data-a11y-tokens attribute, and the stylesheet keys on those tokens. Schemes without them paint exactly as
  before.

# 3.0.8

Improvements from a usability review by a university's instructional designers and accessibility specialist (GitHub
issue #4), with fixes for the Boost Union theme.

- The panel stays open while you try settings. Choosing a colour scheme or a profile, or Reset All, brings the panel
  back where you were.
- The site default option comes first everywhere, including the number settings.
- Line width opens under its tile, like the other short settings, instead of on a separate page.
- A profile now replaces your settings: anything it does not set goes back to the site default. The confirmation says
  so.
- Under a colour scheme, hovered and current menu items and course tabs, the navigation bar and its icons, the
  notification and messaging panels, conversations and unread notifications all take the scheme's colours. Before,
  some of them kept the theme's light colours, which left the scheme's light text on a light background.
- Links has a "Standard" option instead of "Off".
- With highlighted links, icons and current-page underlines inside a link follow the link's dark text.
- The large cursor also shows over checkboxes, radio buttons, menus and buttons. Only text fields keep the text cursor.
- Works with Boost Union, including its coloured navigation bars.
