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
 * Tests for the activity icon baseline read from the active theme's compiled CSS.
 *
 * @package    local_accessibility
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_accessibility\local\theme_icons
 */
final class theme_icons_test extends \advanced_testcase {
    /** @var string Boost 5.2+'s declarations, minified as core caches them, with 5.3's dark tint after. */
    private const CSS_COLOURS = ':root{--activityadministration:#da58ef;--activityassessment:#f90086;'
        . '--activitycollaboration:#5b40ff;--activitycommunication:#eb6200;--activitycontent:#0099ad;'
        . '--activityinteractivecontent:#8d3d1b}[data-bs-theme=dark]{--activitycontent:#4db8c6}.activityiconcontainer{width:52px}';

    /** @var string Boost 4.5's declarations: filter chains. */
    private const CSS_FILTERS = ':root{--activityadministration:invert(45%) sepia(46%) saturate(3819%) hue-rotate(260deg)'
        . ' brightness(101%) contrast(87%);--activitycontent:invert(49%) sepia(52%) saturate(4675%) hue-rotate(156deg)'
        . ' brightness(89%) contrast(102%)}';

    /**
     * The memo is per request; every test starts and ends without it.
     */
    protected function setUp(): void {
        parent::setUp();
        theme_icons::reset_cache();
    }

    /**
     * Drop the memo so later tests do not see this test's theme.
     */
    protected function tearDown(): void {
        theme_icons::reset_cache();
        parent::tearDown();
    }

    /**
     * Colour declarations are read as they are; the first declaration of a purpose wins, so a dark-mode tint later in
     * the sheet is ignored.
     */
    public function test_parse_colours(): void {
        $this->assertSame(purpose::BOOST, theme_icons::parse(self::CSS_COLOURS));
    }

    /**
     * A filter chain is evaluated on black; rgb() and short hex are read; a chain that gives no flat colour, and a
     * property that is not a purpose, are skipped.
     */
    public function test_parse_filters_and_forms(): void {
        $got = theme_icons::parse(self::CSS_FILTERS);
        $this->assertSame(['administration', 'content'], array_keys($got));
        foreach ($got as $id => $hex) {
            foreach ([1, 3, 5] as $i) {
                $diff = abs(hexdec(substr($hex, $i, 2)) - hexdec(substr(purpose::BOOST[$id], $i, 2)));
                $this->assertLessThanOrEqual(8, $diff, "$id from the filter chain is $hex");
            }
        }
        $css = ":root {\n  --activitycontent: rgb(0, 153, 173) !important;\n  --activitycommunication: #abc;\n"
            . "  --activitycollaboration: blur(2px);\n  --activityassessment: red;\n  --activityiconcontainer: 1;\n}\n";
        $this->assertSame(['content' => '#0099ad', 'communication' => '#aabbcc'], theme_icons::parse($css));
        $this->assertSame([], theme_icons::parse('body{color:red}'));
    }

    /**
     * With the theme's CSS in core's cache the baseline is what it declares, Boost filling any purpose left out, and
     * the result is kept in the plugin's cache under the theme and revision.
     */
    public function test_baseline_from_cached_css(): void {
        $this->resetAfterTest();
        $theme = \theme_config::load('boost');
        $theme->set_css_content_cache(':root{--activitycontent:#336699}');
        $expected = ['content' => '#336699'] + purpose::BOOST;
        $this->assertSame($expected, theme_icons::baseline($theme));
        $this->assertSame($expected, \cache::make('local_accessibility', 'iconbaseline')->get(self::key($theme)));
    }

    /**
     * Without compiled CSS, and theme designer mode off, Boost's table stands in and nothing is cached, so the real
     * values are read on the next request.
     */
    public function test_baseline_without_css(): void {
        $this->resetAfterTest();
        $theme = \theme_config::load('boost');
        \cache::make('core', 'postprocessedcss')->delete($theme->get_css_cache_key());
        $this->assertSame(purpose::BOOST, theme_icons::baseline($theme));
        $this->assertFalse(\cache::make('local_accessibility', 'iconbaseline')->has(self::key($theme)));
        $this->assertFalse(\cache::make('local_accessibility', 'iconbaseline')->get(self::key($theme)));
    }

    /**
     * In theme designer mode the CSS is compiled, and the Boost of this Moodle declares all six purposes.
     */
    public function test_baseline_designer_mode_compiles(): void {
        global $CFG;
        $this->resetAfterTest();
        $CFG->themedesignermode = 1;
        $theme = \theme_config::load('boost');
        \cache::make('core', 'postprocessedcss')->delete($theme->get_css_cache_key());
        $baseline = theme_icons::baseline($theme);
        $this->assertSame(purpose::ids(), array_keys($baseline));
        foreach ($baseline as $id => $hex) {
            foreach ([1, 3, 5] as $i) {
                $diff = abs(hexdec($hex[$i] . $hex[$i + 1]) - hexdec(substr(purpose::BOOST[$id], $i, 2)));
                $this->assertLessThanOrEqual(8, $diff, "Boost paints $id $hex");
            }
        }
    }

    /**
     * The baseline is memoised for the request; reset_cache() lets a changed sheet through.
     */
    public function test_memo(): void {
        $this->resetAfterTest();
        $theme = \theme_config::load('boost');
        $theme->set_css_content_cache(':root{--activitycontent:#336699}');
        $this->assertSame('#336699', theme_icons::baseline($theme)['content']);
        $theme->set_css_content_cache(':root{--activitycontent:#663399}');
        \cache::make('local_accessibility', 'iconbaseline')->purge();
        $this->assertSame('#336699', theme_icons::baseline($theme)['content'], 'memoised');
        theme_icons::reset_cache();
        $this->assertSame('#663399', theme_icons::baseline($theme)['content']);
    }

    /**
     * Under a scheme the page's style attribute carries icon colours derived from the theme's declared colour.
     */
    public function test_html_attributes_use_the_baseline(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        \local_accessibility\preferences::set('colour', 'vscodehc');
        $theme = \theme_config::load('boost');
        $theme->set_css_content_cache(':root{--activitycontent:#336699}');
        $expected = \local_accessibility\colour\scheme::presets()['vscodehc']->icon_colours(['content' => '#336699']);
        $style = \local_accessibility\preferences::html_attributes()['style'];
        $this->assertStringContainsString('--a11y-icon-content:' . $expected['content'], $style);
        $this->assertStringNotContainsString(
            '--a11y-icon-content:' . \local_accessibility\colour\scheme::presets()['vscodehc']->icon_colours()['content'],
            $style
        );
    }

    /**
     * The plugin cache key for a theme, as theme_icons builds it.
     *
     * @param \theme_config $theme
     * @return string
     */
    private static function key(\theme_config $theme): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $theme->name . '_' . theme_get_revision());
    }
}
