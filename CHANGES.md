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

# 3.0.7

- Images, Dimmed and Hidden, now also apply to photos shown as background images, such as the photo tiles of the
  Tiles course format and the course cards on the Dashboard. Before, those photos stayed at full strength (GitHub
  issue #3). Hidden removes the photo and keeps the tile and its title. Dimmed fades the photo but not the text on it,
  and the photo comes back to full strength while you point at or focus the tile.
