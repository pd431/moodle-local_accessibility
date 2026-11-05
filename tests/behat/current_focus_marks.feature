@local @local_accessibility @javascript @la_marks
Feature: Current and focus each have one mark under the VS Code scheme
  In order to tell where I am from where I am tabbing
  As a user of the Dark high contrast (VS Code) scheme
  I need current items boxed in solid orange on the scheme's surfaces, keyboard focus in dashed orange, and the
  pointer to show the scheme's hover surface as under every other scheme

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | Sam       | Student  |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | course | name   |
      | page     | C1     | Page 1 |
    And the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |

  Scenario: The current course index entry has the solid box
    Given I am on the "Page 1" "page activity" page logged in as "student1"
    Then the computed "box-shadow" of ".courseindex-item.pageitem" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"
    And the computed "color" of ".courseindex-item.pageitem .courseindex-link" should be "rgb(255, 255, 255)"

  Scenario: Keyboard focus is a dashed ring inside the item, and a current item keeps its solid box around it
    Given I log in as "student1"
    When I give ".primary-navigation [aria-current]" keyboard focus
    Then the computed "outline-style" of ".primary-navigation [aria-current]" should be "dashed"
    And the computed "outline-color" of ".primary-navigation [aria-current]" should be "rgb(243, 133, 24)"
    And the computed "outline-offset" of ".primary-navigation [aria-current]" should be "-4px"
    And the computed "box-shadow" of ".primary-navigation [aria-current]" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"
    When the main region contains core controls
    Then the "#la-test-link" element should show a keyboard focus indicator
    When I give "#la-test-link" keyboard focus
    Then the computed "outline-style" of "#la-test-link" should be "dashed"
    And the computed "outline-offset" of "#la-test-link" should be "-2px"

  Scenario: Pointing at a course tab shows the scheme's hover surface and no ring
    Given the site is running Moodle version 5.2 or lower
    And I am on the "C1" course page logged in as "student1"
    When I hover ".secondary-navigation .nav-tabs .nav-link:not(.active)" "css_element"
    Then the computed "background-color" of ".secondary-navigation .nav-tabs .nav-link:not(.active)" should be "rgb(17, 17, 17)"
    And the computed "outline-style" of ".secondary-navigation .nav-tabs .nav-link:not(.active)" should be "none"

  Scenario: The course tabs are boxed in cyan, neighbours share one edge, and the current tab is boxed in orange on the hover surface
    Given the site is running Moodle version 5.2 or lower
    And I am on the "C1" course page logged in as "student1"
    Then the computed "border-top-color" of ".secondary-navigation .nav-tabs .nav-link:not(.active)" should be "rgb(111, 195, 223)"
    And the computed "border-right-color" of ".secondary-navigation .nav-tabs .nav-link:not(.active)" should be "rgb(111, 195, 223)"
    And the computed "border-bottom-color" of ".secondary-navigation .nav-tabs" should be "rgb(111, 195, 223)"
    And the computed "border-left-color" of ".secondary-navigation .nav-tabs .nav-item + .nav-item .nav-link:not(.active)" should be "rgba(0, 0, 0, 0)"
    And the computed "border-top-color" of ".secondary-navigation .nav-link.active" should be "rgb(243, 133, 24)"
    And the computed "box-shadow" of ".secondary-navigation .nav-link.active" should be "rgb(243, 133, 24) 0px 0px 0px 1px inset"
    And the computed "background-color" of ".secondary-navigation .nav-link.active" should be "rgb(17, 17, 17)"

  Scenario: On Moodle 5.3 the current navigation pill is boxed in orange on its surface, with no dot
    Given the site is running Moodle version 5.3 or higher
    And I am on the "C1" course page logged in as "student1"
    Then the computed "box-shadow" of ".secondary-navigation .mds-nav-pill--selected" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"
    # The current pill is a theme-coloured control: surface2 (#0c141f) at rest, the hover surface under the pointer.
    And the computed "background-color" of ".secondary-navigation .mds-nav-pill--selected" should be "rgb(12, 20, 31)"
    And the computed "color" of ".secondary-navigation .mds-nav-pill--selected" should be "rgb(255, 255, 255)"
    And the computed "display" of ".secondary-navigation .mds-nav-pill--selected .mds-nav-pill__indicator" should be "none"
    And the computed "color" of ".secondary-navigation .mds-nav-pill:not(.mds-nav-pill--selected)" should be "rgb(255, 255, 255)"
    When I hover ".secondary-navigation .mds-nav-pill:not(.mds-nav-pill--selected)" "css_element"
    Then the computed "outline-style" of ".secondary-navigation .mds-nav-pill:not(.mds-nav-pill--selected)" should be "none"
    And the computed "background-color" of ".secondary-navigation .mds-nav-pill:not(.mds-nav-pill--selected)" should be "rgb(17, 17, 17)"

  Scenario: The Focus ring tile's thickness still applies, dashed
    Given the following "user preferences" exist:
      | user     | preference                | value |
      | student1 | local_accessibility_focus | thick |
    And I log in as "student1"
    When the main region contains core controls
    And I give "#la-test-link" keyboard focus
    Then the computed "outline-style" of "#la-test-link" should be "dashed"
    And the computed "outline-width" of "#la-test-link" should be "5px"
    And the computed "outline-offset" of "#la-test-link" should be "-5px"
    And the computed "box-shadow" of "#la-test-link" should be "none"
