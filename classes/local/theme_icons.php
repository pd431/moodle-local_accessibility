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

namespace local_accessibility\local;

use local_accessibility\colour\contrast;
use local_accessibility\colour\cssfilter;
use local_accessibility\colour\purpose;

/**
 * The activity purpose colours the active theme paints: the baseline the schemes lift or lower.
 *
 * Every supported Boost, and every theme built on it, publishes one custom property per purpose on :root,
 * --activity<purpose>. On Moodle 4.5 its value is the CSS filter chain the black monologo is painted with; on 5.2
 * and later it is the colour itself (5.3's dark mode adds a tinted second declaration). Boost Union writes its
 * configured colours into the same Sass variables, so they reach the same properties. Reading those properties
 * back from the theme's compiled CSS gives the colour each theme actually paints, without a table per theme.
 *
 * The compiled CSS is taken from core's postprocessedcss cache, which styles.php fills; nothing is compiled on a
 * page request (except in theme designer mode, where that cost is the developer's). While the cache is empty
 * Boost's table stands in and nothing is remembered, so the next request reads the real values.
 *
 * @package    local_accessibility
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class theme_icons {
    /** @var array<string, array<string, string>> Baselines memoised per cache key for the request. */
    private static array $memo = [];

    /**
     * The six purpose colours the theme that renders the page paints.
     *
     * @param \theme_config|null $theme the theme to read; the page's theme (or, in a web service, the user's) when null
     * @return array<string, string> purpose => normalised colour, every purpose present
     */
    public static function baseline(?\theme_config $theme = null): array {
        global $CFG;
        $theme ??= self::rendering_theme();
        if ($theme === null) {
            return purpose::BOOST;
        }
        $key = self::cache_key($theme);
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }
        $cache = \cache::make('local_accessibility', 'iconbaseline');
        $colours = $cache->get($key);
        if (is_array($colours)) {
            return self::$memo[$key] = $colours;
        }
        if ($theme->has_css_cached_content()) {
            $css = $theme->get_css_cached_content();
        } else if (!empty($CFG->themedesignermode)) {
            $css = $theme->get_css_content();
        } else {
            return purpose::BOOST;      // Not cached: the next request will find what styles.php builds.
        }
        $colours = self::parse((string) $css) + purpose::BOOST;
        $cache->set($key, $colours);
        return self::$memo[$key] = $colours;
    }

    /**
     * The purpose colours declared in a stylesheet, as --activity<purpose>: a hex or rgb() colour, or a filter chain
     * applied to black. The first declaration of each purpose wins; one that is neither is skipped.
     *
     * @param string $css compiled CSS
     * @return array<string, string> purpose => normalised colour, for the purposes found
     */
    public static function parse(string $css): array {
        $pattern = '/--activity(' . implode('|', purpose::ids()) . ')\s*:\s*([^;{}]+?)\s*(?:!important\s*)?[;}]/i';
        if (!preg_match_all($pattern, $css, $m, PREG_SET_ORDER)) {
            return [];
        }
        $out = [];
        foreach ($m as [, $id, $value]) {
            $id = strtolower($id);
            if (isset($out[$id])) {
                continue;
            }
            $hex = self::colour($value);
            if ($hex !== null) {
                $out[$id] = $hex;
            }
        }
        return $out;
    }

    /**
     * Drop the per-request memo (between unit tests).
     *
     * @return void
     */
    public static function reset_cache(): void {
        self::$memo = [];
    }

    /**
     * A declared value as a colour: #rgb, #rrggbb, rgb()/rgba() with opaque integer channels, or a filter chain on
     * black.
     *
     * @param string $value
     * @return string|null
     */
    private static function colour(string $value): ?string {
        $value = trim($value);
        $hex = contrast::normalise($value);
        if ($hex !== null) {
            return $hex;
        }
        $rgb = '/^rgba?\(\s*(\d{1,3})\s*[, ]\s*(\d{1,3})\s*[, ]\s*(\d{1,3})\s*(?:[,\/]\s*(?:1|1\.0|100%)\s*)?\)$/i';
        if (preg_match($rgb, $value, $c)) {
            if (max($c[1], $c[2], $c[3]) <= 255) {
                return sprintf('#%02x%02x%02x', $c[1], $c[2], $c[3]);
            }
            return null;
        }
        return cssfilter::apply($value);
    }

    /**
     * The theme rendering this request, or null where none can be known safely (see colour_mode).
     *
     * @return \theme_config|null
     */
    private static function rendering_theme(): ?\theme_config {
        global $CFG, $PAGE, $USER;
        if (!(defined('AJAX_SCRIPT') && AJAX_SCRIPT) && !(defined('WS_SERVER') && WS_SERVER)) {
            return $PAGE->theme;
        }
        $name = !empty($CFG->allowuserthemes) && !empty($USER->theme) ? $USER->theme : ($CFG->theme ?? '');
        return $name === '' ? null : \theme_config::load($name);
    }

    /**
     * Cache key: theme name and theme revision, so a purge or a theme change refreshes the baseline. Only simple
     * key characters (designer mode's revision is -1).
     *
     * @param \theme_config $theme
     * @return string
     */
    private static function cache_key(\theme_config $theme): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $theme->name . '_' . theme_get_revision());
    }
}
