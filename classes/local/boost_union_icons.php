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

use local_accessibility\colour\purpose;

/**
 * Boost Union adapter: the activity icons Boost Union repaints, aimed at the colour scheme's filters.
 *
 * Boost Union can give an activity module another purpose (activitypurpose<modname>) and, on its Moodle 4.5 line,
 * tint the icons in the timeline, upcoming events, recently accessed items and activities blocks. It does both with
 * rules on the icon's img that set filter with !important, and an overridden module's icon container keeps its
 * default purpose class. Under a colour scheme the scheme's icon filter (styles.css section 7) already outranks those
 * rules; this adapter adds, for each icon Boost Union repaints, a rule on Boost Union's own selectors that takes the
 * filter of the purpose Boost Union paints it in, so the icon keeps that purpose's hue at the scheme's contrast. A
 * module moved to "other" keeps Boost Union's look (no filter). Boost Union's flavours are not read.
 *
 * This is the only file that names Boost Union's settings and selectors. Without Boost Union it emits nothing.
 *
 * @package    local_accessibility
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class boost_union_icons {
    /** @var string The theme this adapter is for. */
    public const THEME = 'boost_union';

    /**
     * @var string Under a scheme only, with three ids' weight: one more than the scheme's own icon rule (two), so the
     *      purpose Boost Union paints wins over the container's default purpose class, and more than Boost Union's
     *      heaviest selector (#page-header, one id).
     */
    private const PREFIX = 'html[data-a11y-colour]:not(#la-weight) body:not(#la-weight):not(#la-weight) ';

    /** @var array<string, string> Boost Union's block tint settings (4.5 line) => the block's icon selector, %s the module. */
    private const TINTS = [
        'timelinetintenabled' => '.block_timeline .theme-boost-union-mod_%s.activityiconcontainer img',
        'upcomingeventstintenabled' => '.block_calendar_upcoming .theme-boost-union-mod_%s.activityiconcontainer img',
        'recentlyaccesseditemstintenabled' => '.block_recentlyaccesseditems .theme-boost-union-%s.activityiconcontainer img',
    ];

    /** @var string Boost Union's "yes" for its select settings. */
    private const YES = 'yes';

    /**
     * The rules for the theme rendering the page, cached per theme and theme revision (Boost Union's settings purge
     * the theme caches, which moves the revision).
     *
     * @param \theme_config $theme
     * @return string CSS, empty unless the theme is Boost Union or built on it
     */
    public static function css(\theme_config $theme): string {
        if ($theme->name !== self::THEME && !in_array(self::THEME, (array) $theme->parents, true)) {
            return '';
        }
        $key = preg_replace('/[^a-zA-Z0-9_]/', '_', $theme->name . '_' . theme_get_revision());
        $cache = \cache::make('local_accessibility', 'iconoverrides');
        $css = $cache->get($key);
        if (is_string($css)) {
            return $css;
        }
        $modules = [];
        foreach (get_module_types_names() as $modname => $name) {
            $modules[$modname] = [
                'purpose' => (string) (plugin_supports('mod', $modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER)
                    ?: MOD_PURPOSE_OTHER),
                'name' => (string) $name,
            ];
        }
        $css = self::rules($modules, (array) get_config('theme_boost_union'));
        $cache->set($key, $css);
        return $css;
    }

    /**
     * The rules for a set of modules and Boost Union's settings, as Boost Union composes its own (its
     * theme_boost_union_get_scss_for_activity_icon_purpose()).
     *
     * @param array<string, array{purpose: string, name: string}> $modules modname => default purpose and display name
     * @param array<string, string> $config Boost Union's settings
     * @return string CSS
     */
    public static function rules(array $modules, array $config): string {
        $css = '';
        foreach ($modules as $modname => $module) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/', (string) $modname)) {
                continue;
            }
            $default = $module['purpose'];
            $chosen = (string) ($config['activitypurpose' . $modname] ?? '');
            $overridden = $chosen !== '' && $chosen !== $default
                && (in_array($chosen, purpose::ids(), true) || $chosen === MOD_PURPOSE_OTHER);
            $painted = $overridden ? $chosen : $default;
            if (!in_array($painted, purpose::ids(), true)) {
                continue;       // Other: Boost Union draws it without a filter, and so does the scheme.
            }
            $selectors = [];
            if ($overridden) {
                $selectors[] = '.activity.modtype_' . $modname . ' .activityiconcontainer.courseicon img';
                $selectors[] = $modname === 'lti'
                    ? '.modchoosercontainer [class*="modicon_' . $modname . '"].activityiconcontainer img'
                    : '.modchoosercontainer .modicon_' . $modname . '.activityiconcontainer img';
                $selectors[] = '#page-header .modicon_' . $modname . '.activityiconcontainer img';
                $selectors[] = '#page-course-overview #' . $modname . '_overview_title .activityiconcontainer img';
            }
            foreach (self::TINTS as $setting => $selector) {
                if (($config[$setting] ?? '') === self::YES) {
                    $selectors[] = sprintf($selector, $modname);
                }
            }
            if (($config['activitiestintenabled'] ?? '') === self::YES) {
                $selectors[] = '.block_activity_modules .content .icon[title="' . self::css_string($module['name']) . '"]';
            }
            if (!$selectors) {
                continue;
            }
            $css .= implode(',', array_map(fn($s) => self::PREFIX . $s, $selectors))
                . '{filter:url("#la-icon-' . $painted . '") !important}';
        }
        return $css;
    }

    /**
     * A value for a double-quoted CSS string inside a <style> element: quotes, backslashes, angle brackets and
     * control characters as CSS escapes.
     *
     * @param string $value
     * @return string
     */
    private static function css_string(string $value): string {
        return preg_replace_callback('/["\\\\<>\x00-\x1f\x7f]/', fn($m) => '\\' . dechex(ord($m[0])) . ' ', $value);
    }
}
