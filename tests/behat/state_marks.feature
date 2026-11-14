@local @local_accessibility @javascript @la_state_marks
Feature: Every state keeps a mark under a colour scheme
  In order to tell which item is current, chosen, changed, disabled, flagged or hidden under my colour scheme
  As a user who chose a scheme
  I need each state to look different from its neighbour by more than hue

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | Sam       | Student  |

  Scenario: Two elements that differ only by a slightly different dark fill do not look different
    Given I log in as "student1"
    When the HTML "<div id='la-a' style='background:#292929;color:#fff'>A</div><div id='la-b' style='background:#383838;color:#fff'>B</div>" is added to the main region
    Then the "#la-a" element should not look different from the "#la-b" element

  Scenario: A Bootstrap 5 table fill counts as the cell's background
    Given I log in as "student1"
    When the HTML "<table class='table'><tbody><tr><td id='la-fill' style='box-shadow: inset 0 0 0 9999px #000; color: #fff'>On</td></tr></tbody></table>" is added to the main region
    Then the "#la-fill" element should have a text contrast of at least 7:1

  Scenario Outline: Current, chosen, changed, disabled, status and hidden states keep a mark under <scheme>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    And the main region contains Moodle state markup
    # Chosen and current.
    Then the "#la-st-page-current" element should look different from the "#la-st-page-other" element
    And the "#la-st-crumb-current" element should look different from the "#la-st-crumb-link" element
    And the "#la-st-list-active" element should look different from the "#la-st-list-other" element
    And the "#la-st-toggle-pressed" element should look different from the "#la-st-toggle-unpressed" element
    And the "#la-st-tox-pressed" element should look different from the "#la-st-tox-plain" element
    And the "#la-st-tox-disabled" element should look different from the "#la-st-tox-plain" element
    And the "#la-st-tab-current" element should look different from the "#la-st-tab-other" element
    And the "#la-st-row-ticked" element should look different from the "#la-st-row-plain" element
    And the "#la-st-row-active" element should look different from the "#la-st-row-plain" element
    # Changed.
    And the "#la-st-cell-quickgrade" element should look different from the "#la-st-cell-plain" element
    And the "#la-st-cell-overridden" element should look different from the "#la-st-cell-plain" element
    And the "#la-st-cell-quickgrade" element should look different from the "#la-st-row-ticked" element
    # Disabled.
    And the "#la-st-menu-disabled-button" element should look different from the "#la-st-menu-enabled" element
    And the "#la-st-menu-disabled-link" element should look different from the "#la-st-menu-enabled" element
    And the "#la-st-page-disabled" element should look different from the "#la-st-page-other" element
    And the "#la-st-tab-disabled" element should look different from the "#la-st-tab-other" element
    And the "#la-st-field-disabled" element should look different from the "#la-st-field-editable" element
    And the "#la-st-field-readonly" element should look different from the "#la-st-field-editable" element
    And the "#la-st-select-disabled" element should look different from the "#la-st-select-editable" element
    # Status.
    And the "#la-st-grade-pass" element should look different from the "#la-st-grade-plain" element
    And the "#la-st-grade-fail" element should look different from the "#la-st-grade-plain" element
    And the "#la-st-grade-pass" element should look different from the "#la-st-grade-fail" element
    And the "#la-st-status-submitted" element should look different from the "#la-st-status-none" element
    And the "#la-st-status-draft" element should look different from the "#la-st-status-none" element
    And the "#la-st-text-warning" element should look different from the "#la-st-text-plain" element
    And the "#la-st-text-danger" element should look different from the "#la-st-text-plain" element
    And the "#la-st-text-success" element should look different from the "#la-st-text-plain" element
    And the "#la-st-error" element should look different from the "#la-st-label" element
    And the "#la-st-field-invalid" element should look different from the "#la-st-field-editable" element
    And the "#la-st-row-success" element should look different from the "#la-st-row-plain" element
    And the "#la-st-row-warning" element should look different from the "#la-st-row-plain" element
    And the "#la-st-row-danger" element should look different from the "#la-st-row-plain" element
    And the "#la-st-row-warning" element should look different from the "#la-st-row-danger" element
    # Progress and hidden.
    And the "#la-st-progress-bar" element should look different from the "#la-st-progress" element
    And the "#la-st-dimmed" element should look different from the "#la-st-text-plain" element
    And the "#la-st-dimmed" element should have a text contrast of at least 7:1

    Examples:
      | scheme       |
      | highcontrast |
      | yellowblack  |
      | blackwhite   |
      | cream        |
      | dark         |
      | vscodehc     |

  Scenario Outline: A focused text link shows a focus mark in the mark colour under <scheme>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    And the main region contains Moodle state markup
    Then the "#la-st-link" element should show a keyboard focus indicator
    And the computed "background-color" of "#la-st-link" should be "rgba(0, 0, 0, 0)"

    Examples:
      | scheme       |
      | highcontrast |
      | cream        |

  Scenario Outline: A ticked row in the assignment grading table stands out under <scheme>
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname |
      | student2 | Pat       | Pupil    |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "activities" exist:
      | activity | course | name  | assignsubmission_onlinetext_enabled |
      | assign   | C1     | Essay | 1                                   |
    And the following "user preferences" exist:
      | user  | preference                 | value    |
      | admin | local_accessibility_colour | <scheme> |
    And I am on the "Essay" "assign activity" page logged in as "admin"
    And I navigate to "Submissions" in current page administration
    When I click on "Select Sam Student" "checkbox"
    Then the "tr.selectedrow td.email" element should look different from the "tr.unselectedrow td.email" element

    Examples:
      | scheme       |
      | highcontrast |
      | blackwhite   |

  Scenario: With no scheme chosen the state marks change nothing
    Given I log in as "student1"
    And the main region contains Moodle state markup
    Then the computed "box-shadow" of "#la-st-page-other" should be "none"
    And the computed "opacity" of "#la-st-menu-disabled-link" should be "1"
    And the computed "font-style" of "#la-st-dimmed" should be "normal"
