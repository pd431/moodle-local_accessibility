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
 * Tests for colour schemes.
 *
 * @package    local_accessibility
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_accessibility\colour\scheme
 * @covers     \local_accessibility\colour\adjust
 */
final class scheme_test extends \advanced_testcase {
    /**
     * Every preset reaches 7:1 for text and links on every shade (spec §6.1).
     */
    public function test_presets_pass_on_every_shade(): void {
        foreach (scheme::presets() as $id => $s) {
            $this->assertGreaterThanOrEqual(contrast::AAA, $s->worst_text(), "$id text");
            $this->assertGreaterThanOrEqual(contrast::AAA, $s->worst_link(), "$id link");
            if ($s->border !== null) {
                continue;       // Tiers are told apart by the border: see test_border_scheme_edges().
            }
            // On light pages field = page by design, so at least 4 distinct shades.
            $this->assertGreaterThanOrEqual(4, count(array_unique($s->ramp)), "$id ramp has distinct shades");
        }
    }

    /**
     * A scheme with a border colour may share one shade across its tiers; the border then stands out from every shade
     * by at least 3:1 (WCAG 1.4.11), and so does its focus colour.
     */
    public function test_border_scheme_edges(): void {
        $seen = 0;
        foreach (scheme::presets() as $id => $s) {
            if ($s->border === null) {
                continue;
            }
            $seen++;
            $this->assertGreaterThanOrEqual(3.0, contrast::worst($s->border, $s->ramp), "$id border");
            $this->assertNotNull($s->focus, "$id focus");
            $this->assertGreaterThanOrEqual(3.0, contrast::worst($s->focus, $s->ramp), "$id focus");
        }
        $this->assertSame(1, $seen, 'one border scheme: vscodehc');
    }

    /**
     * The VS Code dark high contrast preset keeps its palette exactly, declares all three tokens and emits them.
     */
    public function test_vscodehc_preset(): void {
        $s = scheme::presets()['vscodehc'];
        $this->assertTrue($s->exact);
        $this->assertSame('dark', $s->mode);
        $this->assertSame('#000000', $s->ramp['page']);
        $this->assertSame('#000000', $s->ramp['surface1']);
        $this->assertSame('#000000', $s->ramp['field']);
        $this->assertSame('#ffffff', $s->text);
        $this->assertSame('#8bcaff', $s->link);
        $this->assertGreaterThanOrEqual(70, contrast::apca_worst($s->link, $s->ramp), 'link Lc 70 on every shade');
        $this->assertSame('#6fc3df', $s->border);
        $this->assertSame('#f38518', $s->focus);
        $this->assertSame('#f38518', $s->active);
        $this->assertGreaterThanOrEqual(3.0, contrast::worst($s->active, $s->ramp), 'active box 3:1 on every shade');
        $this->assertSame('#f3f518', $s->selection);
        $this->assertSame('#000000', $s->selectiontext);
        $this->assertSame(['border', 'focus', 'selection', 'active'], $s->tokens());
        $css = $s->css_properties();
        $this->assertStringContainsString('--a11y-border:#6fc3df;', $css);
        $this->assertStringContainsString('--a11y-focus:#f38518;', $css);
        $this->assertStringContainsString('--a11y-active:#f38518;', $css);
        $this->assertStringContainsString('--a11y-selection:#f3f518;--a11y-selection-text:#000000;', $css);
        $this->assertMatchesRegularExpression('/^(--a11y-[a-z0-9-]+:#[0-9a-f]{6};)+$/', $css);
        // Every other preset, and a custom scheme, declares no token and emits none.
        foreach (scheme::presets() + ['c' => scheme::custom('#14202b', '#e8eef3', '#8cc8ff', false)] as $id => $o) {
            if ($id === 'vscodehc') {
                continue;
            }
            $this->assertSame([], $o->tokens(), $id);
            $this->assertStringNotContainsString('--a11y-border', $o->css_properties(), $id);
            $this->assertStringNotContainsString('--a11y-focus', $o->css_properties(), $id);
            $this->assertStringNotContainsString('--a11y-active', $o->css_properties(), $id);
            $this->assertNull($o->active, $id);
            $this->assertStringNotContainsString('--a11y-selection', $o->css_properties(), $id);
        }
    }

    /**
     * fixed() checks every colour, needs the whole ramp, takes selection as a pair, and refuses a palette under 7:1.
     */
    public function test_fixed_refusals(): void {
        $ramp = ['page' => '#000000', 'surface1' => '#000000', 'surface2' => '#0c141f', 'field' => '#000000',
            'hover' => '#111111'];
        $ok = scheme::fixed('t', $ramp, '#FFFFFF', '#21a6ff');
        $this->assertSame('#ffffff', $ok->text, 'normalised');
        $this->assertNull($ok->border);
        $this->assertSame([], $ok->tokens());
        $cases = [
            'bad hex' => fn() => scheme::fixed('t', $ramp, '#ffffff', '#21a6ff', '#6fc3df;x'),
            'missing tier' => fn() => scheme::fixed('t', ['page' => '#000000'], '#ffffff', '#21a6ff'),
            'half selection' => fn() => scheme::fixed('t', $ramp, '#ffffff', '#21a6ff', null, null, '#f3f518'),
            'link under 7:1' => fn() => scheme::fixed('t', $ramp, '#ffffff', '#0f6cbf'),
            'text under 7:1 on hover' => fn() => scheme::fixed('t', ['hover' => '#777777'] + $ramp, '#ffffff', '#21a6ff'),
            'active under 3:1' => fn() => scheme::fixed('t', $ramp, '#ffffff', '#21a6ff', null, null, null, null, '#333333'),
        ];
        foreach ($cases as $name => $case) {
            try {
                $case();
                $this->fail("$name accepted");
            } catch (\invalid_parameter_exception $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Every preset keeps its surface tiers visibly distinct, including pure black (spec §6.3).
     */
    public function test_preset_tiers_distinct(): void {
        foreach (scheme::presets() as $id => $s) {
            if ($s->border !== null) {
                continue;       // Tiers are told apart by the border: see test_border_scheme_edges().
            }
            $r = $s->ramp;
            $this->assertCount(3, array_unique([$r['page'], $r['surface1'], $r['surface2']]), "$id tiers distinct");
            $this->assertGreaterThanOrEqual(1.15, contrast::ratio($r['page'], $r['surface2']), "$id page vs surface2");
        }
    }

    /**
     * Light pages step darker, dark pages step lighter (spec §6.3).
     */
    public function test_ramp_direction(): void {
        $light = adjust::ramp('#fbf3df');
        $this->assertLessThan(contrast::luminance('#fbf3df'), contrast::luminance($light['surface2']));
        $dark = adjust::ramp('#14202b');
        $this->assertGreaterThan(contrast::luminance('#14202b'), contrast::luminance($dark['surface2']));
        $this->assertSame('light', adjust::mode('#fbf3df'));
        $this->assertSame('dark', adjust::mode('#000000'));
    }

    /**
     * White text on a mid blue: the background darkens until 7:1 holds (spec §6.2 worked example).
     */
    public function test_auto_adjust_moves_background(): void {
        $s = scheme::custom('#3a6ea5', '#ffffff', '#ffe08a', false);
        $this->assertGreaterThanOrEqual(contrast::AAA, $s->worst_text());
        $this->assertGreaterThanOrEqual(contrast::AAA, $s->worst_link());
        $this->assertLessThan(contrast::luminance('#3a6ea5'), contrast::luminance($s->ramp['page']));
        $hue = fn(string $h): float => oklch::from_hex($h)[2];
        $this->assertEqualsWithDelta($hue('#3a6ea5'), $hue($s->ramp['page']), 6.0, 'hue kept');
        $this->assertFalse($s->exact);
    }

    /**
     * Dark text on a mid blue: the background lightens.
     */
    public function test_auto_adjust_dark_text(): void {
        $s = scheme::custom('#3a6ea5', '#1d2125', '#0b4f8a', false);
        $this->assertGreaterThanOrEqual(contrast::AAA, $s->worst_text());
        $this->assertGreaterThan(contrast::luminance('#3a6ea5'), contrast::luminance($s->ramp['page']));
    }

    /**
     * Exact colours are kept as given, even under 7:1.
     */
    public function test_exact_keeps_colours(): void {
        $s = scheme::custom('#3a6ea5', '#1d2125', '#ffe08a', true);
        $this->assertSame('#3a6ea5', $s->ramp['page']);
        $this->assertSame('#1d2125', $s->text);
        $this->assertTrue($s->exact);
        $this->assertLessThan(contrast::AAA, $s->worst_text());
    }

    /**
     * Below 1.5:1 is refused even when exact.
     */
    public function test_floor_refused(): void {
        $this->expectException(\invalid_parameter_exception::class);
        scheme::custom('#ffffff', '#fafafa', '#0000c0', true);
    }

    /**
     * Invalid hex is refused.
     */
    public function test_invalid_hex_refused(): void {
        $this->expectException(\invalid_parameter_exception::class);
        scheme::custom('#fff;}', '#000000', '#0000c0', false);
    }

    /**
     * JSON round trip, and tampered JSON is rejected.
     */
    public function test_json(): void {
        $s = scheme::custom('#fbf3df', '#1f1f1f', '#0b4f8a', true);
        $back = scheme::from_json($s->to_json());
        $this->assertSame($s->ramp, $back->ramp);
        $this->assertNull(scheme::from_json('{"v":1,"bg":"#fff;x","text":"#000000","link":"#0000c0","exact":true}'));
        $this->assertNull(scheme::from_json('not json'));
        $this->assertNull(scheme::from_json(str_repeat('a', 5000)));
        // Non-string colours must give null without a PHP warning.
        $this->assertNull(scheme::from_json('{"v":1,"bg":["x"],"text":"#000000","link":"#0000c0","exact":true}'));
        $this->assertNull(scheme::from_json('{"v":1,"bg":"#ffffff","text":{"a":1},"link":"#0000c0","exact":true}'));
        $this->assertNull(scheme::from_json('{"v":1,"bg":"#ffffff","text":"#000000","link":5,"exact":true}'));
        // Only a real boolean true means exact.
        $loose = scheme::from_json('{"v":1,"bg":"#3a6ea5","text":"#1d2125","link":"#ffe08a","exact":"false"}');
        $this->assertFalse($loose->exact);
        $this->assertGreaterThanOrEqual(contrast::AAA, $loose->worst_text());
    }

    /**
     * CSS output contains only custom properties with hex values.
     */
    public function test_css_properties_safe(): void {
        $css = scheme::presets()['cream']->css_properties();
        $this->assertMatchesRegularExpression('/^(--a11y-[a-z0-9-]+:#[0-9a-f]{6};)+$/', $css);
    }

    /**
     * Every preset's six activity icon colours reach APCA Lc 75 and 4.5:1 on page, surface1 and surface2, keep
     * Boost's hue, and are emitted as --a11y-icon-<purpose>.
     */
    public function test_icon_colours(): void {
        foreach (scheme::presets() as $id => $s) {
            $colours = $s->icon_colours();
            $this->assertSame(purpose::ids(), array_keys($colours), $id);
            $surfaces = [$s->ramp['page'], $s->ramp['surface1'], $s->ramp['surface2']];
            foreach ($colours as $p => $hex) {
                $this->assertGreaterThanOrEqual(purpose::TARGET, contrast::apca_worst($hex, $surfaces), "$id $p");
                $this->assertGreaterThanOrEqual(purpose::WCAG, contrast::worst($hex, $surfaces), "$id $p WCAG 2");
                [, $c, $h] = oklch::from_hex($hex);
                [, , $boosth] = oklch::from_hex(purpose::BOOST[$p]);
                $diff = abs($h - $boosth);
                $this->assertLessThan(3, min($diff, 360 - $diff), "$id $p keeps the hue");
                $this->assertGreaterThan(0.03, $c, "$id $p keeps some chroma");
                $this->assertStringContainsString("--a11y-icon-$p:$hex;", $s->css_properties(), $id);
            }
            $this->assertCount(6, array_unique($colours), "$id icon colours are distinct");
        }
    }

    /**
     * A purpose colour that already reaches the target is left as Boost made it; one no lightness of its hue can
     * bring to the target falls back to the text colour.
     */
    public function test_icon_colours_untouched_or_fallback(): void {
        $white = ['page' => '#ffffff', 'surface1' => '#ffffff', 'surface2' => '#ffffff'];
        $this->assertSame('#8d3d1b', purpose::colours($white, '#000000')['interactivecontent'], 'Lc 85 on white');
        $grey = ['page' => '#808080', 'surface1' => '#808080', 'surface2' => '#808080'];
        foreach (purpose::colours($grey, '#101010') as $p => $hex) {
            $this->assertSame('#101010', $hex, "$p falls back to the text colour on mid grey");
        }
    }

    /**
     * A theme's own purpose colours are the baseline: kept exactly where they already pass both measures, moved in
     * lightness but not hue where they do not, with Boost's table filling any purpose the theme leaves out.
     */
    public function test_icon_colours_from_baseline(): void {
        $white = ['page' => '#ffffff', 'surface1' => '#ffffff', 'surface2' => '#ffffff'];
        $passes = purpose::colours($white, '#000000', ['content' => '#1b4f72']);
        $this->assertSame('#1b4f72', $passes['content'], 'already passes on white, kept as the theme made it');
        $this->assertSame('#8d3d1b', $passes['interactivecontent'], 'left out of the baseline, Boost fills in');

        $dark = scheme::presets()['vscodehc'];
        $colours = $dark->icon_colours(['content' => '#336699']);
        $surfaces = [$dark->ramp['page'], $dark->ramp['surface1'], $dark->ramp['surface2']];
        $this->assertNotSame('#336699', $colours['content']);
        $this->assertGreaterThanOrEqual(purpose::TARGET, contrast::apca_worst($colours['content'], $surfaces));
        $this->assertGreaterThanOrEqual(purpose::WCAG, contrast::worst($colours['content'], $surfaces));
        [, , $h] = oklch::from_hex($colours['content']);
        [, , $baseh] = oklch::from_hex('#336699');
        $diff = abs($h - $baseh);
        $this->assertLessThan(3, min($diff, 360 - $diff), 'keeps the theme hue, not Boost\'s teal');
        $this->assertSame($dark->icon_colours()['assessment'], $colours['assessment'], 'other purposes unchanged');
        $css = $dark->css_properties(['content' => '#336699']);
        $this->assertStringContainsString('--a11y-icon-content:' . $colours['content'] . ';', $css);
    }
}
