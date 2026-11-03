# 3.1.0

- New colour scheme, Dark high contrast (VS Code): the palette of Visual Studio Code's Dark High Contrast theme. Every
  surface is black and told apart by a cyan border, text is white, links are VS Code's blue, keyboard focus shows an
  orange ring whatever the Focus ring tile says (Ring and Extra thick keep their thickness), and selected text is
  yellow on black. Every text and link pair is at or above 7:1.
- Under a colour scheme, links are coloured by purpose. An anchor in a navigation landmark or a menu, tab, tree or
  toolbar widget, a tab, menu item, dropdown item, page link, nav link or badge, a sort link in a table head, or a
  block link in or around a heading (section titles, course cards) takes the text colour with no forced underline.
  Every other anchor, on every page, keeps the link colour and underline. Before, every anchor but buttons was
  painted in the link colour, so the navbar, the course index and the tabs read as a wall of links.
- Colour schemes can now declare a border, a focus and a selection colour. The page lists the ones a scheme declares
  in a data-a11y-tokens attribute, and the stylesheet keys on those tokens. Schemes without them paint exactly as
  before.

# 3.0.7

- Images, Dimmed and Hidden, now also apply to photos shown as background images, such as the photo tiles of the
  Tiles course format and the course cards on the Dashboard. Before, those photos stayed at full strength (GitHub
  issue #3). Hidden removes the photo and keeps the tile and its title. Dimmed fades the photo but not the text on it,
  and the photo comes back to full strength while you point at or focus the tile.
