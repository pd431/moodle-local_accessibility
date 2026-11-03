<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_accessibility\colour;

use invalid_parameter_exception;

/**
 * A colour scheme: surface ramp, text and link colours (spec §6).
 *
 * @package    local_accessibility
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class scheme {
    /**
     * Constructor; use custom(), fixed() or presets().
     *
     * @param string $id preset id or 'custom'
     * @param string $mode 'light' or 'dark'
     * @param array $ramp page, surface1, surface2, field, hover
     * @param string $text
     * @param string $link
     * @param bool $exact true when the user chose exact colours over auto-adjust
     * @param string|null $border edge colour that tells surfaces apart, for a scheme whose tiers share one shade
     * @param string|null $focus keyboard focus ring colour
     * @param string|null $selection background of selected text
     * @param string|null $selectiontext colour of selected text
     * @param string|null $active box around the current navigation item (VS Code's contrastActiveBorder)
     */
    private function __construct(
        /** @var string Preset id or "custom". */
        public readonly string $id,
        /** @var string "light" or "dark". */
        public readonly string $mode,
        /** @var array Surface ramp: page, surface1, surface2, field, hover. */
        public readonly array $ramp,
        /** @var string Text colour. */
        public readonly string $text,
        /** @var string Link colour. */
        public readonly string $link,
        /** @var bool True when exact colours were chosen. */
        public readonly bool $exact,
        /** @var string|null Border colour, or null when edges take the text colour as before. */
        public readonly ?string $border = null,
        /** @var string|null Focus ring colour, or null for the Focus ring tile's own colours. */
        public readonly ?string $focus = null,
        /** @var string|null Selected text background, or null for the browser's. */
        public readonly ?string $selection = null,
        /** @var string|null Selected text colour, or null for the browser's. */
        public readonly ?string $selectiontext = null,
        /** @var string|null Current-item box colour, or null for the theme's own current marks. */
        public readonly ?string $active = null,
    ) {
    }

    /**
     * Build a preset from fixed colours: no auto-adjust and no derived ramp. For palettes designed as a whole, such as
     * an editor's high-contrast theme, whose surfaces share one shade and are told apart by a border.
     *
     * Every colour is checked as #rrggbb. The text and link must reach 7:1 on every ramp shade: a fixed preset
     * promises what auto-adjust guarantees for the others.
     *
     * @param string $id preset id
     * @param array $ramp page, surface1, surface2, field, hover
     * @param string $text
     * @param string $link
     * @param string|null $border
     * @param string|null $focus
     * @param string|null $selection selected text background; needs $selectiontext
     * @param string|null $selectiontext
     * @param string|null $active box around the current navigation item; must reach 3:1 on every shade
     * @return self
     * @throws invalid_parameter_exception invalid hex, text or link below 7:1 on a shade, active below 3:1
     */
    public static function fixed(
        string $id,
        array $ramp,
        string $text,
        string $link,
        ?string $border = null,
        ?string $focus = null,
        ?string $selection = null,
        ?string $selectiontext = null,
        ?string $active = null,
    ): self {
        $colours = $ramp + ['text' => $text, 'link' => $link, 'border' => $border, 'focus' => $focus,
            'selection' => $selection, 'selectiontext' => $selectiontext, 'active' => $active];
        foreach ($colours as $name => $hex) {
            if ($hex === null) {
                continue;
            }
            $colours[$name] = contrast::normalise($hex);
            if ($colours[$name] === null) {
                throw new invalid_parameter_exception("$name must be #rrggbb");
            }
        }
        foreach (['page', 'surface1', 'surface2', 'field', 'hover'] as $tier) {
            if (!isset($colours[$tier])) {
                throw new invalid_parameter_exception("Ramp needs $tier");
            }
        }
        if (($colours['selection'] === null) !== ($colours['selectiontext'] === null)) {
            throw new invalid_parameter_exception('Selection needs both a background and a text colour');
        }
        $ramp = array_intersect_key($colours, array_flip(['page', 'surface1', 'surface2', 'field', 'hover']));
        $s = new self(
            $id,
            adjust::mode($ramp['page']),
            $ramp,
            $colours['text'],
            $colours['link'],
            true,
            $colours['border'],
            $colours['focus'],
            $colours['selection'],
            $colours['selectiontext'],
            $colours['active']
        );
        if ($s->worst_text() < contrast::AAA || $s->worst_link() < contrast::AAA) {
            throw new invalid_parameter_exception('A fixed preset must reach 7:1 on every shade');
        }
        if ($s->active !== null && contrast::worst($s->active, $s->ramp) < 3.0) {
            throw new invalid_parameter_exception('The active colour must reach 3:1 on every shade');
        }
        return $s;
    }

    /**
     * Build a custom scheme, auto-adjusting to 7:1 unless exact (spec §6.2).
     *
     * @param string $bg
     * @param string $text
     * @param string $link
     * @param bool $exact
     * @param string $id
     * @return self
     * @throws invalid_parameter_exception invalid hex, or below the floor
     */
    public static function custom(string $bg, string $text, string $link, bool $exact, string $id = 'custom'): self {
        $bg = contrast::normalise($bg);
        $text = contrast::normalise($text);
        $link = contrast::normalise($link);
        if ($bg === null || $text === null || $link === null) {
            throw new invalid_parameter_exception('Colours must be #rrggbb');
        }
        $ramp = adjust::ramp($bg);
        if ($exact) {
            $s = new self($id, adjust::mode($bg), $ramp, $text, $link, true);
            if ($s->worst_text() < contrast::FLOOR || $s->worst_link() < contrast::FLOOR) {
                throw new invalid_parameter_exception('Colours are below the 1.5:1 floor');
            }
            return $s;
        }
        $newtext = adjust::towards($text, $ramp, contrast::AAA);
        if ($newtext === null) {
            // Move the page away from the text until the text's hue can pass (spec §6.2 step 2).
            [$l, $c, $h] = oklch::from_hex($bg);
            $step = contrast::luminance($text) > contrast::luminance($bg) ? -0.01 : 0.01;
            for ($i = 1; $i <= 100 && $newtext === null; $i++) {
                $ramp = adjust::ramp(oklch::to_hex([$l + $step * $i, $c, $h]));
                $newtext = adjust::towards($text, $ramp, contrast::AAA);
            }
            $newtext ??= self::best_bw($ramp);
        }
        $newlink = adjust::towards($link, $ramp, contrast::AAA) ?? self::best_bw($ramp);
        return new self($id, adjust::mode($ramp['page']), $ramp, $newtext, $newlink, false);
    }

    /**
     * Black or white, whichever contrasts more with the ramp.
     *
     * @param array $ramp
     * @return string
     */
    private static function best_bw(array $ramp): string {
        return contrast::worst('#000000', $ramp) >= contrast::worst('#ffffff', $ramp) ? '#000000' : '#ffffff';
    }

    /**
     * Built-in presets, built through auto-adjust so they always pass (spec §6.1).
     *
     * @return array<string, self>
     */
    public static function presets(): array {
        static $cache = null;
        return $cache ??= [
            'highcontrast' => self::custom('#000000', '#ffffff', '#ffff00', false, 'highcontrast'),
            'yellowblack' => self::custom('#000000', '#ffff00', '#00ffff', false, 'yellowblack'),
            // Visual Studio Code's Dark High Contrast theme (vscode: themes/hc_black.json and the hcDark defaults of
            // platform/theme/common/colors): black everywhere, told apart by contrastBorder; textLink, focusBorder,
            // the editor selection colours, and contrastActiveBorder (the same orange) around the current item.
            // Floating widgets take editorWidget.background; hover is a 7% white, the
            // most that keeps the link at 7:1 (VS Code's own list hover is 10%).
            'vscodehc' => self::fixed(
                'vscodehc',
                ['page' => '#000000', 'surface1' => '#000000', 'surface2' => '#0c141f', 'field' => '#000000',
                    'hover' => '#111111'],
                '#ffffff',
                '#21a6ff',
                '#6fc3df',
                '#f38518',
                '#f3f518',
                '#000000',
                '#f38518'
            ),
            'blackwhite' => self::custom('#ffffff', '#000000', '#0000c0', false, 'blackwhite'),
            'cream' => self::custom('#fbf3df', '#1f1f1f', '#0b4f8a', false, 'cream'),
            'dark' => self::custom('#14202b', '#e8eef3', '#8cc8ff', false, 'dark'),
        ];
    }

    /**
     * Lowest text ratio over the ramp.
     *
     * @return float
     */
    public function worst_text(): float {
        return contrast::worst($this->text, $this->ramp);
    }

    /**
     * Lowest link ratio over the ramp.
     *
     * @return float
     */
    public function worst_link(): float {
        return contrast::worst($this->link, $this->ramp);
    }

    /**
     * Stored form of a custom scheme.
     *
     * @return string
     */
    public function to_json(): string {
        return json_encode(['v' => 1, 'bg' => $this->ramp['page'], 'text' => $this->text,
            'link' => $this->link, 'exact' => $this->exact]);
    }

    /**
     * Parse a stored custom scheme; anything invalid gives null.
     *
     * @param string $json
     * @return self|null
     */
    public static function from_json(string $json): ?self {
        if (strlen($json) > 500) {
            return null;
        }
        $d = json_decode($json, true);
        if (!is_array($d) || ($d['v'] ?? 0) !== 1 || !isset($d['bg'], $d['text'], $d['link'])) {
            return null;
        }
        if (!is_string($d['bg']) || !is_string($d['text']) || !is_string($d['link'])) {
            return null;
        }
        try {
            return self::custom($d['bg'], $d['text'], $d['link'], ($d['exact'] ?? false) === true);
        } catch (invalid_parameter_exception $e) {
            return null;
        }
    }

    /**
     * The extra colours this scheme declares beyond page, text and link: 'border', 'focus' and 'selection', in that
     * order. The page's data-a11y-tokens attribute lists them, so the stylesheet keys on the token, never on an id.
     *
     * @return string[]
     */
    public function tokens(): array {
        $tokens = [];
        if ($this->border !== null) {
            $tokens[] = 'border';
        }
        if ($this->focus !== null) {
            $tokens[] = 'focus';
        }
        if ($this->selection !== null) {
            $tokens[] = 'selection';
        }
        if ($this->active !== null) {
            $tokens[] = 'active';
        }
        return $tokens;
    }

    /**
     * Activity purpose colours under this scheme (see purpose).
     *
     * @return array<string, string> purpose => normalised colour
     */
    public function icon_colours(): array {
        return purpose::colours($this->ramp, $this->text);
    }

    /**
     * Custom properties for the html style attribute. Only validated hex values are emitted; the extra tokens only
     * when the scheme declares them. The icon colours are --a11y-icon-<purpose>, read by the panel's SVG filters.
     *
     * @return string e.g. "--a11y-page:#fbf3df;..."
     */
    public function css_properties(): string {
        $props = $this->ramp + ['text' => $this->text, 'link' => $this->link, 'border' => $this->border,
            'focus' => $this->focus, 'selection' => $this->selection, 'selection-text' => $this->selectiontext,
            'active' => $this->active];
        foreach ($this->icon_colours() as $id => $hex) {
            $props['icon-' . $id] = $hex;
        }
        $out = '';
        foreach ($props as $name => $hex) {
            if ($hex !== null) {
                $out .= '--a11y-' . $name . ':' . $hex . ';';
            }
        }
        return $out;
    }
}
