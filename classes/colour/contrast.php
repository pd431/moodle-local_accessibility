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
 * WCAG 2 relative luminance and contrast ratio.
 *
 * @package    local_accessibility
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class contrast {
    /** @var float Enhanced contrast (WCAG 1.4.6, AAA). */
    public const AAA = 7.0;
    /** @var float Below this a custom scheme is refused (spec §6.2). */
    public const FLOOR = 1.5;

    /**
     * Normalise a hex colour to lowercase #rrggbb.
     *
     * @param string $hex
     * @return string|null null when the input is not a 3- or 6-digit hex colour
     */
    public static function normalise(string $hex): ?string {
        if (preg_match('/^#([0-9a-f]{3})\z/i', $hex, $m)) {
            $hex = '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
        }
        return preg_match('/^#[0-9a-f]{6}\z/i', $hex) ? strtolower($hex) : null;
    }

    /**
     * Relative luminance as defined by WCAG 2.
     *
     * @param string $hex normalised colour
     * @return float 0..1
     */
    public static function luminance(string $hex): float {
        $l = [];
        foreach ([1, 3, 5] as $i) {
            $c = hexdec(substr($hex, $i, 2)) / 255;
            $l[] = $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }
        return 0.2126 * $l[0] + 0.7152 * $l[1] + 0.0722 * $l[2];
    }

    /**
     * Contrast ratio between two colours.
     *
     * @param string $a normalised colour
     * @param string $b normalised colour
     * @return float 1..21
     */
    public static function ratio(string $a, string $b): float {
        $la = self::luminance($a);
        $lb = self::luminance($b);
        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /**
     * APCA lightness contrast (SAPC-4 0.0.98G): Lc, signed. Positive for dark text on a light background, negative
     * for light text on a dark background; 0 when the pair is within the algorithm's noise. Used for the activity
     * icon colours only, because WCAG 2 over-rates saturated colour on a dark page (Boost's purpose colours reach
     * 3:1 to 7:1 on black by WCAG 2 and Lc 13 to 43 by APCA); text and links keep WCAG 2 ratios throughout.
     *
     * @param string $fg normalised colour (the icon or text)
     * @param string $bg normalised colour (what is behind it)
     * @return float Lc, -108..106
     */
    public static function apca(string $fg, string $bg): float {
        $ytxt = self::apca_y($fg);
        $ybg = self::apca_y($bg);
        if ($ybg > $ytxt) {
            $sapc = ($ybg ** 0.56 - $ytxt ** 0.57) * 1.14;
            return $sapc < 0.1 ? 0.0 : ($sapc - 0.027) * 100;
        }
        $sapc = ($ybg ** 0.65 - $ytxt ** 0.62) * 1.14;
        return $sapc > -0.1 ? 0.0 : ($sapc + 0.027) * 100;
    }

    /**
     * Smallest |Lc| of a foreground against several backgrounds.
     *
     * @param string $fg normalised colour
     * @param string[] $backgrounds normalised colours
     * @return float
     */
    public static function apca_worst(string $fg, array $backgrounds): float {
        return min(array_map(fn(string $bg): float => abs(self::apca($fg, $bg)), $backgrounds));
    }

    /**
     * How far a foreground is from passing both measures against every background: the smaller of |Lc| / $lc and
     * ratio / $ratio, over the backgrounds. 1.0 or more means it passes both. APCA is the stronger reading on a dark
     * page, but WCAG 2 is what the schemes promise, so the icon colours are held to both; normalising the two to a
     * target of 1 lets adjust::towards search once for the smallest lightness change that satisfies both.
     *
     * @param string $fg normalised colour
     * @param string[] $backgrounds normalised colours
     * @param float $lc the Lc that counts as a pass
     * @param float $ratio the WCAG 2 ratio that counts as a pass
     * @return float
     */
    public static function dual_worst(string $fg, array $backgrounds, float $lc = 75.0, float $ratio = 4.5): float {
        return min(self::apca_worst($fg, $backgrounds) / $lc, self::worst($fg, $backgrounds) / $ratio);
    }

    /**
     * APCA's estimated screen luminance, with its black soft clamp.
     *
     * @param string $hex normalised colour
     * @return float
     */
    private static function apca_y(string $hex): float {
        $y = 0.0;
        foreach ([1 => 0.2126729, 3 => 0.7151522, 5 => 0.0721750] as $i => $w) {
            $y += $w * ((hexdec(substr($hex, $i, 2)) / 255) ** 2.4);
        }
        return $y < 0.022 ? $y + (0.022 - $y) ** 1.414 : $y;
    }

    /**
     * Lowest ratio of a foreground against several backgrounds.
     *
     * @param string $fg normalised colour
     * @param string[] $backgrounds normalised colours
     * @return float
     */
    public static function worst(string $fg, array $backgrounds): float {
        return min(array_map(fn(string $bg): float => self::ratio($fg, $bg), $backgrounds));
    }
}
