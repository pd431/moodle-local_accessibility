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

/**
 * Puts the floating launcher and the panel last in the page, after the footer and the theme's corner buttons.
 *
 * CSS anchor positioning only lets a fixed box anchor to a corner button whose fixed box comes before it in the page
 * (Chromium applies this; Firefox is lenient). Moodle renders plugins' footer HTML before the theme's footer, so the
 * launcher and the panel (which anchors to the launcher) are moved to the end of <body>, launcher first. Nothing is
 * measured: their places stay in the stylesheet, which falls back to core's resting spot without this script.
 * Moving a node takes focus from inside it, so whatever had focus in them (the panel reopened after a reload puts
 * focus back on the chosen scheme or profile) gets it back.
 *
 * @module     local_accessibility/launcherplace
 * @copyright  2026 Pierre (University of Exeter)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {
    const launcher = document.querySelector('.local-accessibility-launcher');
    if (!launcher) {
        return;
    }
    const panel = document.getElementById('local-accessibility-panel');
    const focused = document.activeElement;
    document.body.append(launcher);
    if (panel) {
        document.body.append(panel);
    }
    if (focused && focused !== document.activeElement && (launcher.contains(focused) || panel?.contains(focused))) {
        focused.focus({preventScroll: true});
    }
};
