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
 * The colour a CSS filter chain yields on a flat colour.
 *
 * Boost paints each black monologo activity icon with a chain of the shorthand filter functions (invert, sepia,
 * saturate, hue-rotate, brightness, contrast) and, on Moodle 4.5, publishes that chain rather than a colour as the
 * purpose's --activity<purpose> custom property. Applying the chain to black here recovers the colour the browser
 * shows. Each function is the colour matrix the Filter Effects Module gives for it, applied in sRGB with the
 * result clamped to 0..1 after every function, as a browser does between primitives.
 *
 * @package    local_accessibility
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cssfilter {
    /**
     * Apply a filter chain to a colour.
     *
     * @param string $chain e.g. "invert(45%) sepia(46%) saturate(3819%) hue-rotate(260deg) brightness(101%)"
     * @param string $hex normalised start colour, black by default (the monologo's fill)
     * @return string|null the resulting #rrggbb, or null when the chain holds a function this cannot evaluate
     *      (blur, drop-shadow, url, opacity) or does not parse
     */
    public static function apply(string $chain, string $hex = '#000000'): ?string {
        $chain = trim($chain);
        if ($chain === '' || $chain === 'none') {
            return $hex;
        }
        if (!preg_match_all('/([a-z-]+)\(([^()]*)\)/i', $chain, $calls, PREG_SET_ORDER)) {
            return null;
        }
        // Everything between the calls must be whitespace: anything else is not a filter list.
        if (trim(preg_replace('/[a-z-]+\([^()]*\)/i', '', $chain)) !== '') {
            return null;
        }
        $rgb = [];
        foreach ([1, 3, 5] as $i) {
            $rgb[] = hexdec(substr($hex, $i, 2)) / 255;
        }
        foreach ($calls as [, $name, $arg]) {
            $m = self::matrix(strtolower($name), trim($arg));
            if ($m === null) {
                return null;
            }
            $out = [];
            for ($row = 0; $row < 3; $row++) {
                $v = $m[$row][0] * $rgb[0] + $m[$row][1] * $rgb[1] + $m[$row][2] * $rgb[2] + $m[$row][3];
                $out[] = min(1.0, max(0.0, $v));
            }
            $rgb = $out;
        }
        return sprintf('#%02x%02x%02x', (int) round($rgb[0] * 255), (int) round($rgb[1] * 255), (int) round($rgb[2] * 255));
    }

    /**
     * The 3x4 colour matrix (3x3 plus an offset column) of one shorthand filter function.
     *
     * @param string $name function name, lowercase
     * @param string $arg its argument as written
     * @return array|null null for a function with no colour matrix or an unparsable argument
     */
    private static function matrix(string $name, string $arg): ?array {
        if ($name === 'hue-rotate') {
            $a = self::angle($arg);
            if ($a === null) {
                return null;
            }
            $c = cos($a);
            $s = sin($a);
            return [
                [0.213 + $c * 0.787 - $s * 0.213, 0.715 - $c * 0.715 - $s * 0.715, 0.072 - $c * 0.072 + $s * 0.928, 0],
                [0.213 - $c * 0.213 + $s * 0.143, 0.715 + $c * 0.285 + $s * 0.140, 0.072 - $c * 0.072 - $s * 0.283, 0],
                [0.213 - $c * 0.213 - $s * 0.787, 0.715 - $c * 0.715 + $s * 0.715, 0.072 + $c * 0.928 + $s * 0.072, 0],
            ];
        }
        $p = self::amount($arg);
        if ($p === null) {
            return null;
        }
        switch ($name) {
            case 'invert':
                // Each channel becomes p + c (1 - 2p): c when p is 0, 1 - c when p is 1.
                return [[1 - 2 * $p, 0, 0, $p], [0, 1 - 2 * $p, 0, $p], [0, 0, 1 - 2 * $p, $p]];
            case 'brightness':
                return [[$p, 0, 0, 0], [0, $p, 0, 0], [0, 0, $p, 0]];
            case 'contrast':
                return [[$p, 0, 0, 0.5 - 0.5 * $p], [0, $p, 0, 0.5 - 0.5 * $p], [0, 0, $p, 0.5 - 0.5 * $p]];
            case 'saturate':
                return [
                    [0.213 + 0.787 * $p, 0.715 - 0.715 * $p, 0.072 - 0.072 * $p, 0],
                    [0.213 - 0.213 * $p, 0.715 + 0.285 * $p, 0.072 - 0.072 * $p, 0],
                    [0.213 - 0.213 * $p, 0.715 - 0.715 * $p, 0.072 + 0.928 * $p, 0],
                ];
            case 'grayscale':
                $q = 1 - min(1.0, $p);
                return [
                    [0.2126 + 0.7874 * $q, 0.7152 - 0.7152 * $q, 0.0722 - 0.0722 * $q, 0],
                    [0.2126 - 0.2126 * $q, 0.7152 + 0.2848 * $q, 0.0722 - 0.0722 * $q, 0],
                    [0.2126 - 0.2126 * $q, 0.7152 - 0.7152 * $q, 0.0722 + 0.9278 * $q, 0],
                ];
            case 'sepia':
                $q = 1 - min(1.0, $p);
                return [
                    [0.393 + 0.607 * $q, 0.769 - 0.769 * $q, 0.189 - 0.189 * $q, 0],
                    [0.349 - 0.349 * $q, 0.686 + 0.314 * $q, 0.168 - 0.168 * $q, 0],
                    [0.272 - 0.272 * $q, 0.534 - 0.534 * $q, 0.131 + 0.869 * $q, 0],
                ];
            default:
                // Blur, drop-shadow, opacity and url() have no flat-colour result.
                return null;
        }
    }

    /**
     * A number or percentage as a factor: "45%" and "0.45" are both 0.45. Negative values are clamped to 0, as the
     * specification says.
     *
     * @param string $arg
     * @return float|null
     */
    private static function amount(string $arg): ?float {
        if (!preg_match('/^([+-]?(?:\d+\.?\d*|\.\d+))(%?)$/', $arg, $m)) {
            return null;
        }
        $v = (float) $m[1];
        return max(0.0, $m[2] === '%' ? $v / 100 : $v);
    }

    /**
     * An angle in radians from deg, grad, rad or turn.
     *
     * @param string $arg
     * @return float|null
     */
    private static function angle(string $arg): ?float {
        if (!preg_match('/^([+-]?(?:\d+\.?\d*|\.\d+))(deg|grad|rad|turn)?$/', $arg, $m)) {
            return null;
        }
        $v = (float) $m[1];
        return match ($m[2] ?? 'deg') {
            'grad' => $v * M_PI / 200,
            'rad' => $v,
            'turn' => $v * 2 * M_PI,
            default => $v * M_PI / 180,
        };
    }
}
