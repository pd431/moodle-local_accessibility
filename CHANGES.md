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
  line under the bar instead of a border around each tab. The Focus ring tile's Ring and Extra thick keep their
  thickness, dashed.
- Colour schemes can now declare a border, a focus, a selection and an active colour. The page lists the ones a scheme declares
  in a data-a11y-tokens attribute, and the stylesheet keys on those tokens. Schemes without them paint exactly as
  before.

# 3.0.7

- Images, Dimmed and Hidden, now also apply to photos shown as background images, such as the photo tiles of the
  Tiles course format and the course cards on the Dashboard. Before, those photos stayed at full strength (GitHub
  issue #3). Hidden removes the photo and keeps the tile and its title. Dimmed fades the photo but not the text on it,
  and the photo comes back to full strength while you point at or focus the tile.
