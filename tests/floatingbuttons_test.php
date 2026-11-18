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

/**
 * The floating launcher's place: registration with local_floatingbuttons, and its own place without it.
 *
 * @package    local_accessibility
 * @copyright  2026 Pierre (University of Exeter)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class floatingbuttons_test extends \advanced_testcase {
    /**
     * The launcher registers for slot 1 unless it lives in the user menu only.
     */
    public function test_registration(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/accessibility/lib.php');
        $this->resetAfterTest();
        foreach (['both', 'floating'] as $mode) {
            set_config('launcher', $mode, 'local_accessibility');
            $defs = local_accessibility_floatingbuttons();
            $this->assertCount(1, $defs, $mode);
            $this->assertSame('launcher', $defs[0]['id']);
            $this->assertSame(1, $defs[0]['defaultslot']);
        }
        set_config('launcher', 'menu', 'local_accessibility');
        $this->assertSame([], local_accessibility_floatingbuttons());
    }

    /**
     * Without local_floatingbuttons the launcher takes .la-launcher-own: one gap above core's resting spot for the "?",
     * on its centre line, lifted over a sticky footer as core lifts the "?"; the base rule sets no position, so the
     * framework's slot classes are not fought.
     */
    public function test_own_place(): void {
        $css = preg_replace('~/\*.*?\*/~s', '', file_get_contents(__DIR__ . '/../styles.css'));
        preg_match('/\.btn\.local-accessibility-launcher \{([^}]*)\}/', $css, $base);
        $this->assertStringNotContainsString('inset-', $base[1], 'the base rule leaves the place to a slot or to la-launcher-own');
        preg_match('/\.btn\.local-accessibility-launcher\.la-launcher-own \{([^}]*)\}/', $css, $own);
        $this->assertStringContainsString('inset-block-end: calc(2rem + 2.25rem + 0.75rem)', $own[1]);
        $this->assertStringContainsString('inset-inline-end: calc(2rem + 1.125rem)', $own[1]);
        $this->assertStringContainsString('translate: 50% 0', $own[1]);
        $this->assertStringContainsString('body.hasstickyfooter .btn.local-accessibility-launcher.la-launcher-own', $css);
        $this->assertStringContainsString('.btn.local-accessibility-launcher.la-launcher-own:dir(rtl)', $css);
        $this->assertStringContainsString('body:has(.la-launcher-own) .drawer.drawer-right.show', $css,
            'the own drawer anchor only while an own launcher is on the page');
    }
}
