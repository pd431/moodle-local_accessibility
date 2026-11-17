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
    And the computed "opacity" of "#la-st-field-readonly" should be "1"
    And the "#la-st-select-disabled" element should look different from the "#la-st-select-editable" element
    # A disabled text field, textarea or select looks like a read-only one: not faded, text at full contrast.
    And the computed "opacity" of "#la-st-field-disabled" should be "1"
    And the computed "opacity" of "#la-st-select-disabled" should be "1"
    And the computed "opacity" of "#la-st-area-disabled" should be "1"
    And the computed "border-top-width" of "#la-st-field-disabled" should match that of "#la-st-field-readonly"
    And the computed "border-top-color" of "#la-st-field-disabled" should match that of "#la-st-field-readonly"
    And the computed "background-color" of "#la-st-field-disabled" should match that of "#la-st-field-readonly"
    And the computed "color" of "#la-st-field-disabled" should match that of "#la-st-field-readonly"
    And the "#la-st-field-disabled" element should have a text contrast of at least 4.5:1
    And the "#la-st-field-readonly" element should have a text contrast of at least 4.5:1
    And the "#la-st-select-disabled" element should have a text contrast of at least 4.5:1
    # Other disabled controls still fade.
    And the computed "opacity" of "#la-st-btn-disabled" should be "0.6"
    And the "#la-st-btn-disabled" element should look different from the "#la-st-btn-enabled" element
    # Status. Status text has a dot before it, so it looks different from plain text; one status against another
    # differs by the dot's hue alone (accepted). A bordered status cell must look different from a plain one.
    And the "#la-st-grade-pass" element should look different from the "#la-st-grade-plain" element
    And the "#la-st-grade-fail" element should look different from the "#la-st-grade-plain" element
    And the "#la-st-grade-pass" element should differ in hue from the "#la-st-grade-fail" element
    # An assignment submission's status is said in words; core's fills are under 3:1, so it takes no state mark.
    And the computed "box-shadow" of "#la-st-status-submitted" should be "none"
    And the computed "box-shadow" of "#la-st-status-draft" should be "none"
    And the "#la-st-status-submitted" element should not look different from the "#la-st-status-none" element
    And the "#la-st-status-draft" element should have a text contrast of at least 4.5:1
    And the "#la-st-status-submitted" element should have a text contrast of at least 4.5:1
    And the "#la-st-text-warning" element should look different from the "#la-st-text-plain" element
    And the "#la-st-text-danger" element should look different from the "#la-st-text-plain" element
    And the "#la-st-text-success" element should look different from the "#la-st-text-plain" element
    And the "#la-st-error" element should look different from the "#la-st-label" element
    And the "#la-st-field-invalid" element should look different from the "#la-st-field-editable" element
    And the "#la-st-row-success" element should look different from the "#la-st-row-plain" element
    And the "#la-st-row-warning" element should look different from the "#la-st-row-plain" element
    And the "#la-st-row-danger" element should look different from the "#la-st-row-plain" element
    And the "#la-st-row-warning" element should differ in hue from the "#la-st-row-danger" element
    # Badges and pills: painted like alerts, a coloured one differs from a grey one by its marker.
    And the "#la-st-badge-ok" element should look different from the "#la-st-badge-grey" element
    And the "#la-st-badge-bad" element should look different from the "#la-st-badge-grey" element
    And the "#la-st-pill-warn" element should look different from the "#la-st-badge-grey" element
    And the "#la-st-badge-ok" element should differ in hue from the "#la-st-badge-bad" element
    And the "#la-st-badge-ok" element should have a text contrast of at least 7:1
    And the "#la-st-badge-bad" element should have a text contrast of at least 7:1
    And the "#la-st-pill-warn" element should have a text contrast of at least 7:1
    # Progress and hidden.
    And the "#la-st-progress-bar" element should look different from the "#la-st-progress" element
    And the "#la-st-dimmed" element should look different from the "#la-st-text-plain" element
    And the "#la-st-dimmed" element should have a text contrast of at least 7:1
    And the "#la-st-text-warning" element should have a text contrast of at least 7:1
    And the "#la-st-text-danger" element should have a text contrast of at least 7:1

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

  Scenario Outline: Only the course index's page item is chosen, while tabs and listbox options keep aria-selected under <scheme>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    When the HTML "<div class='courseindex' role='tree'><div class='courseindex-section' id='la-ci-section' tabindex='0' aria-selected='true'><div class='courseindex-item courseindex-section-title'>General</div><ul><li class='courseindex-item pageitem' id='la-ci-page' aria-selected='false'>Essay</li><li class='courseindex-item' id='la-ci-other' aria-selected='false'>Quiz</li></ul></div></div><ul role='tablist'><li><a role='tab' href='#' id='la-ci-tab' aria-selected='true'>One</a></li><li><a role='tab' href='#' id='la-ci-tab-other' aria-selected='false'>Two</a></li></ul><ul role='listbox'><li role='option' id='la-ci-option' aria-selected='true'>Red</li><li role='option' id='la-ci-option-other' aria-selected='false'>Blue</li></ul>" is added to the main region
    Then the computed "box-shadow" of "#la-ci-section" should be "none"
    And the "#la-ci-page" element should look different from the "#la-ci-other" element
    And the computed "box-shadow" of "#la-ci-other" should be "none"
    And the "#la-ci-tab" element should look different from the "#la-ci-tab-other" element
    And the computed "box-shadow" of "#la-ci-tab-other" should be "none"
    And the "#la-ci-option" element should look different from the "#la-ci-option-other" element
    And the computed "box-shadow" of "#la-ci-option-other" should be "none"

    Examples:
      | scheme       |
      | highcontrast |
      | yellowblack  |

  Scenario: With no scheme chosen the state marks change nothing
    Given I log in as "student1"
    And the main region contains Moodle state markup
    Then the computed "box-shadow" of "#la-st-page-other" should be "none"
    And the computed "opacity" of "#la-st-menu-disabled-link" should be "1"
    And the computed "font-style" of "#la-st-dimmed" should be "normal"
