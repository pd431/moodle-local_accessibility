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
 * Tests for the WCAG contrast and OKLCH helpers.
 *
 * @package    local_accessibility
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_accessibility\colour\contrast
 * @covers     \local_accessibility\colour\oklch
 */
final class contrast_test extends \advanced_testcase {
    /**
     * Known WCAG pairs, values computed 2026-10-02 (spec §6.1).
     *
     * @return array
     */
    public static function pairs(): array {
        return [
            'black on white' => ['#000000', '#ffffff', 21.0],
            'yellow on black' => ['#ffff00', '#000000', 19.56],
            'cream text' => ['#1f1f1f', '#fbf3df', 14.90],
            'cream link' => ['#0b4f8a', '#fbf3df', 7.60],
            'dark text' => ['#e8eef3', '#14202b', 14.12],
            'boost link' => ['#0f6cbf', '#ffffff', 5.36],
            'mid blue white' => ['#ffffff', '#3a6ea5', 5.31],
        ];
    }

    /**
     * Ratios match the WCAG formula to two decimals.
     *
     * @dataProvider pairs
     * @param string $fg
     * @param string $bg
     * @param float $expected
     */
    public function test_ratio(string $fg, string $bg, float $expected): void {
        $this->assertEqualsWithDelta($expected, contrast::ratio($fg, $bg), 0.01);
        $this->assertEqualsWithDelta($expected, contrast::ratio($bg, $fg), 0.01);
    }

    /**
     * Invalid input never reaches the maths.
     */
    public function test_normalise(): void {
        $this->assertSame('#aabbcc', contrast::normalise('#ABC'));
        $this->assertSame('#0b4f8a', contrast::normalise('#0B4F8A'));
        $this->assertNull(contrast::normalise('red'));
        $this->assertNull(contrast::normalise('#12345'));
        $this->assertNull(contrast::normalise('#12345g'));
        $this->assertNull(contrast::normalise('#123456;color:red'));
        // PCRE's $ also matches before a final newline; a stored value must not carry one into the style attribute.
        $this->assertNull(contrast::normalise("#123456\n"));
        $this->assertNull(contrast::normalise("#abc\n"));
    }

    /**
     * APCA reference pairs published with SAPC-4 0.0.98G (apcacontrast.com, "Test values").
     *
     * @return array
     */
    public static function apca_pairs(): array {
        return [
            'grey on white' => ['#888888', '#ffffff', 63.056],
            'white on grey' => ['#ffffff', '#888888', -68.541],
            'black on light grey' => ['#000000', '#aaaaaa', 58.146],
            'light grey on black' => ['#aaaaaa', '#000000', -56.241],
            'navy on pale blue' => ['#112233', '#ddeeff', 91.668],
            'pale blue on navy' => ['#ddeeff', '#112233', -93.068],
        ];
    }

    /**
     * Lc matches the published values to two decimals, with the sign of the polarity.
     *
     * @dataProvider apca_pairs
     * @param string $fg
     * @param string $bg
     * @param float $expected
     */
    public function test_apca(string $fg, string $bg, float $expected): void {
        $this->assertEqualsWithDelta($expected, contrast::apca($fg, $bg), 0.01);
    }

    /**
     * Boost's purpose colours on black: WCAG 2 passes most of them, APCA none at Lc 60. The reason the icon colours
     * are measured with APCA (see purpose).
     */
    public function test_apca_sees_what_wcag_misses(): void {
        $this->assertGreaterThan(6.0, contrast::ratio('#da58ef', '#000000'));
        $this->assertLessThan(45, abs(contrast::apca('#da58ef', '#000000')));
        $this->assertEqualsWithDelta(0.0, contrast::apca('#808080', '#8a8a8a'), 0.001, 'within the noise floor');
        $this->assertEqualsWithDelta(16.7, contrast::apca_worst('#8d3d1b', ['#000000', '#ffffff']), 0.1);
    }

    /**
     * The worst ratio is the minimum over the backgrounds.
     */
    public function test_worst(): void {
        $this->assertEqualsWithDelta(3.92, contrast::worst('#0f6cbf', ['#ffffff', '#000000']), 0.01);
    }

    /**
     * The dual measure is the smaller of the two normalised readings over the backgrounds: 1 or more passes both.
     */
    public function test_dual_worst(): void {
        // Boost's violet on black: WCAG 2 passes 4.5:1 (6.4:1), APCA does not (Lc 43), so APCA binds.
        $apca = contrast::apca_worst('#da58ef', ['#000000']) / 75;
        $this->assertEqualsWithDelta($apca, contrast::dual_worst('#da58ef', ['#000000']), 0.0001);
        $this->assertLessThan(1.0, contrast::dual_worst('#da58ef', ['#000000']));
        // Black on white passes both by a wide margin.
        $this->assertGreaterThan(1.0, contrast::dual_worst('#000000', ['#ffffff']));
        // The pass levels can be moved; at a 21:1 WCAG level nothing passes, and WCAG binds.
        $this->assertLessThan(1.0, contrast::dual_worst('#000000', ['#ffffff'], 75.0, 21.1));
        $this->assertEqualsWithDelta(21.0 / 21.1, contrast::dual_worst('#000000', ['#ffffff'], 75.0, 21.1), 0.001);
        // Over several backgrounds the worst one counts.
        $both = contrast::dual_worst('#0f6cbf', ['#ffffff', '#000000']);
        $this->assertEqualsWithDelta(contrast::dual_worst('#0f6cbf', ['#000000']), $both, 0.0001);
    }

    /**
     * OKLCH round-trips within one step per channel.
     */
    public function test_oklch_roundtrip(): void {
        foreach (['#3a6ea5', '#fbf3df', '#14202b', '#ffff00', '#000000', '#ffffff'] as $hex) {
            $back = oklch::to_hex(oklch::from_hex($hex));
            foreach ([1, 3, 5] as $i) {
                $this->assertLessThanOrEqual(1, abs(hexdec(substr($hex, $i, 2)) - hexdec(substr($back, $i, 2))), $hex);
            }
        }
    }
}
