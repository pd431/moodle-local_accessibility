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

namespace local_accessibility;

use local_accessibility\admin\setting_pixels;
use local_accessibility\output\panel;

/**
 * The floating launcher's place: the "Button height offset" setting, and the stylesheet that uses it.
 *
 * @package    local_accessibility
 * @copyright  2026 Pierre (University of Exeter)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_accessibility\admin\setting_pixels
 */
final class launcher_test extends \advanced_testcase {
    /**
     * The setting takes a whole number of pixels from 0 to 200; the default is 12.
     */
    public function test_setting_range(): void {
        $this->assertSame(12, panel::LAUNCHER_OFFSET);
        $s = new setting_pixels('local_accessibility/launcheroffset', 'x', 'y', panel::LAUNCHER_OFFSET, 0, 200);
        foreach (['0', '12', '200'] as $ok) {
            $this->assertTrue($s->validate($ok), $ok);
        }
        foreach (['-1', '201', '1.5', 'abc', ''] as $bad) {
            $this->assertIsString($s->validate($bad), $bad);
        }
    }

    /**
     * The panel template gets the offset, the default when unset, clamped to 0-200.
     */
    public function test_template_offset(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        $PAGE->set_url('/');
        $context = fn() => (new panel())->export_for_template($PAGE->get_renderer('core'));
        $this->assertSame(12, $context()['launcheroffset']);
        set_config('launcheroffset', 24, 'local_accessibility');
        $this->assertSame(24, $context()['launcheroffset']);
        set_config('launcheroffset', 999, 'local_accessibility');
        $this->assertSame(200, $context()['launcheroffset']);
    }

    /**
     * The launcher sits the offset above the corner button (anchored to the "?" or back-to-top, else core's resting
     * spot), and the panel opens beside it with the same gap.
     */
    public function test_stylesheet(): void {
        $css = file_get_contents(__DIR__ . '/../styles.css');
        $this->assertStringContainsString('--la-offset: var(--la-launcher-offset, 12px);', $css);
        $this->assertStringContainsString('inset-block-end: calc(var(--la-rest-top) + var(--la-offset));', $css);
        $this->assertStringContainsString(':is(.btn-footer-popover, #boost-union-footer-buttons #back-to-top) {', $css);
        $this->assertStringContainsString('anchor-name: --la-corner;', $css);
        $this->assertStringContainsString('inset-block-end: calc(anchor(--la-corner top, var(--la-rest-top)) + var(--la-offset));', $css);
        $this->assertStringContainsString('inset-block-end: min(anchor(--la-launcher bottom,', $css, 'panel bottoms level with the launcher');
        $this->assertStringContainsString('inset-inline-end: calc(anchor(--la-launcher start,', $css, 'panel beside the launcher');
        $this->assertStringContainsString('--la-corner-size: calc(2rem + 0.25rem * (1 + var(--breakpoint-xs, -1)));', $css);
        $this->assertStringContainsString('body.hasstickyfooter {', $css);
        $this->assertFileExists(__DIR__ . '/../amd/src/launcherplace.js');
    }
}
