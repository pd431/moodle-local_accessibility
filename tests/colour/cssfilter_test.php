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
 * Tests for the CSS filter chain evaluator.
 *
 * @package    local_accessibility
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_accessibility\colour\cssfilter
 */
final class cssfilter_test extends \advanced_testcase {
    /**
     * Boost 4.5's six purpose filter chains (theme/boost/scss/moodle/variables.scss, $activity-icon-*-filter) and the
     * -bg colours Boost solved them for.
     *
     * @return array
     */
    public static function boost_chains(): array {
        return [
            'administration' => ['invert(45%) sepia(46%) saturate(3819%) hue-rotate(260deg) brightness(101%) contrast(87%)',
                '#da58ef'],
            'assessment' => ['invert(36%) sepia(98%) saturate(6969%) hue-rotate(315deg) brightness(90%) contrast(119%)',
                '#f90086'],
            'collaboration' => ['invert(25%) sepia(54%) saturate(6226%) hue-rotate(245deg) brightness(100%) contrast(102%)',
                '#5b40ff'],
            'communication' => ['invert(48%) sepia(74%) saturate(4887%) hue-rotate(11deg) brightness(102%) contrast(101%)',
                '#eb6200'],
            'content' => ['invert(49%) sepia(52%) saturate(4675%) hue-rotate(156deg) brightness(89%) contrast(102%)',
                '#0099ad'],
            'interactivecontent' => ['invert(25%) sepia(63%) saturate(1152%) hue-rotate(344deg) brightness(94%) contrast(91%)',
                '#8d3d1b'],
        ];
    }

    /**
     * Each chain applied to black lands within 8 of each channel of the colour Boost solved it for (the chains were
     * fitted by hand, so they are not exact).
     *
     * @dataProvider boost_chains
     * @param string $chain
     * @param string $expected
     */
    public function test_boost_chains(string $chain, string $expected): void {
        $got = cssfilter::apply($chain);
        $this->assertNotNull($got, $chain);
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $got);
        foreach ([1, 3, 5] as $i) {
            $diff = abs(hexdec(substr($got, $i, 2)) - hexdec(substr($expected, $i, 2)));
            $this->assertLessThanOrEqual(8, $diff, "$chain gives $got, want $expected");
        }
    }

    /**
     * Single functions give the colours the specification defines.
     */
    public function test_single_functions(): void {
        $this->assertSame('#ffffff', cssfilter::apply('invert(1)'));
        $this->assertSame('#ffffff', cssfilter::apply('invert(100%)'));
        $this->assertSame('#000000', cssfilter::apply('invert(1)', '#ffffff'));
        $this->assertSame('#808080', cssfilter::apply('brightness(50%)', '#ffffff'));
        $this->assertSame('#808080', cssfilter::apply('contrast(0)', '#123456'));
        $this->assertSame('#ffffff', cssfilter::apply('brightness(2)', '#ffffff'), 'clamped');
        // Grayscale of pure red is its luminance, 0.2126.
        $this->assertSame('#363636', cssfilter::apply('grayscale(1)', '#ff0000'));
        // A full hue rotation is the identity, in any angle unit.
        foreach (['360deg', '400grad', '1turn', '6.283185307rad'] as $turn) {
            $this->assertSame('#3a6ea5', cssfilter::apply("hue-rotate($turn)", '#3a6ea5'), $turn);
        }
    }

    /**
     * No chain, or the keyword none, leaves the colour alone.
     */
    public function test_none(): void {
        $this->assertSame('#123456', cssfilter::apply('none', '#123456'));
        $this->assertSame('#123456', cssfilter::apply('', '#123456'));
        $this->assertSame('#123456', cssfilter::apply('  ', '#123456'));
    }

    /**
     * A chain this cannot evaluate to a flat colour, or that is not a filter list, is null rather than a guess.
     */
    public function test_unsupported(): void {
        $this->assertNull(cssfilter::apply('blur(2px)'));
        $this->assertNull(cssfilter::apply('invert(1) drop-shadow(0 0 2px #000)'));
        $this->assertNull(cssfilter::apply('opacity(50%)'));
        $this->assertNull(cssfilter::apply('url(#la-icon-content)'));
        $this->assertNull(cssfilter::apply('invert(abc)'));
        $this->assertNull(cssfilter::apply('hue-rotate(10px)'));
        $this->assertNull(cssfilter::apply('red'));
        $this->assertNull(cssfilter::apply('invert(1) !important'));
    }
}
