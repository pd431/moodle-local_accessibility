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

namespace local_accessibility\admin;

/**
 * A whole number of pixels within a range, for admin settings.
 *
 * @package    local_accessibility
 * @copyright  2026 Pierre (University of Exeter)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_pixels extends \admin_setting_configtext {
    /** @var int Smallest value accepted. */
    private int $min;

    /** @var int Largest value accepted. */
    private int $max;

    /**
     * Constructor.
     *
     * @param string $name
     * @param \lang_string|string $visiblename
     * @param \lang_string|string $description
     * @param int $default
     * @param int $min
     * @param int $max
     */
    public function __construct(string $name, $visiblename, $description, int $default, int $min, int $max) {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($name, $visiblename, $description, $default, PARAM_INT, 4);
    }

    /**
     * A whole number from min to max.
     *
     * @param string $data
     * @return true|string true, or the error message
     */
    public function validate($data) {
        if (!preg_match('/^\d+$/', trim((string) $data)) || (int) $data < $this->min || (int) $data > $this->max) {
            return get_string('pixelsrange', 'local_accessibility', ['min' => $this->min, 'max' => $this->max]);
        }
        return true;
    }
}
