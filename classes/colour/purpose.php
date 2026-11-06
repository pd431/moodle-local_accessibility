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

/**
 * Activity purpose colours under a scheme: the theme's six, lifted or lowered in lightness until they reach APCA
 * Lc 75 and a WCAG 2 ratio of 4.5:1 against every surface an icon sits on.
 *
 * Boost paints each monologo activity icon with a CSS filter chain that yields one of these colours. On a dark
 * page Boost's reach Lc 13 to 43 (brown and violet are the worst); on cream three of the six fall under Lc 60.
 * Lc 75 is APCA's level for body text; 4.5:1 is the WCAG 2 text level it corresponds to, held as well because
 * WCAG 2 is what the schemes promise everywhere else. The baseline is what the active theme paints
 * (\local_accessibility\local\theme_icons), Boost's table when that cannot be read.
 *
 * @package    local_accessibility
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class purpose {
    /** @var float APCA Lc every icon colour reaches against page, surface1 and surface2. */
    public const TARGET = 75.0;

    /** @var float WCAG 2 ratio every icon colour reaches as well (text level; 1.4.11's 3:1 would never bind). */
    public const WCAG = 4.5;

    /**
     * Boost 4.5's purpose colours (theme/boost/scss/moodle/variables.scss, $activity-icon-*-bg). Names are the
     * class names Boost puts on .activityiconcontainer.
     *
     * @var array<string, string>
     */
    public const BOOST = [
        'administration' => '#da58ef',
        'assessment' => '#f90086',
        'collaboration' => '#5b40ff',
        'communication' => '#eb6200',
        'content' => '#0099ad',
        'interactivecontent' => '#8d3d1b',
    ];

    /**
     * The purpose names, in Boost's order.
     *
     * @return string[]
     */
    public static function ids(): array {
        return array_keys(self::BOOST);
    }

    /**
     * One colour per purpose for a scheme: the baseline where it already passes both measures, else the nearest
     * lightness of the same hue that does, else the scheme's text colour (no lightness of that hue passes).
     *
     * @param array $ramp the scheme's ramp (page, surface1, surface2 are what icons sit on)
     * @param string $text the scheme's text colour, the fallback
     * @param array|null $baseline purpose => normalised colour the theme paints; Boost's table by default, and for
     *      any purpose the baseline leaves out
     * @return array<string, string> purpose => normalised colour
     */
    public static function colours(array $ramp, string $text, ?array $baseline = null): array {
        $surfaces = ['page' => $ramp['page'], 'surface1' => $ramp['surface1'], 'surface2' => $ramp['surface2']];
        $measure = fn(string $fg, array $bgs): float => contrast::dual_worst($fg, $bgs, self::TARGET, self::WCAG);
        $out = [];
        foreach (self::BOOST as $id => $hex) {
            $hex = $baseline[$id] ?? $hex;
            $out[$id] = adjust::towards($hex, $surfaces, 1.0, $measure) ?? $text;
        }
        return $out;
    }
}
