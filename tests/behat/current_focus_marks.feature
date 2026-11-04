@local @local_accessibility @javascript @la_marks
Feature: Current, focus and hover each have one mark under the VS Code scheme
  In order to tell where I am from where I am pointing or tabbing
  As a user of the Dark high contrast (VS Code) scheme
  I need current items boxed in solid orange, focus and hover in dashed orange, and no fills behind them

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

  Scenario: The current course index entry has the solid box and no fill
    Given I am on the "Page 1" "page activity" page logged in as "student1"
    Then the computed "box-shadow" of ".courseindex-item.pageitem" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"
    And the computed "background-color" of ".courseindex-item.pageitem" should be "rgba(0, 0, 0, 0)"
    And the computed "color" of ".courseindex-item.pageitem .courseindex-link" should be "rgb(255, 255, 255)"

  Scenario: Keyboard focus is a dashed ring outside the item, and the current item keeps its solid box under it
    Given I log in as "student1"
    Then the computed "outline-style" of ".primary-navigation .nav-link[aria-current]:focus-visible" should be "dashed"
    And the computed "outline-color" of ".primary-navigation .nav-link[aria-current]:focus-visible" should be "rgb(243, 133, 24)"
    And the computed "box-shadow" of ".primary-navigation .nav-link[aria-current]:focus-visible" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"
    When the main region contains core controls
    Then the "#la-test-link" element should show a keyboard focus indicator
    And the computed "outline-style" of "#la-test-link:focus-visible" should be "dashed"

  Scenario: Pointing at a link or control shows the same dashed ring
    Given I log in as "student1"
    When the main region contains core controls
    And I hover "#la-test-link" "css_element"
    Then the computed "outline-style" of "#la-test-link" should be "dashed"
    And the computed "outline-color" of "#la-test-link" should be "rgb(243, 133, 24)"
    When I hover "#la-test-submit" "css_element"
    Then the computed "outline-style" of "#la-test-submit" should be "dashed"

  Scenario: The course tabs are boxed in cyan, neighbours share one edge, and the current tab is boxed in orange alone
    Given I am on the "C1" course page logged in as "student1"
    Then the computed "border-top-color" of ".secondary-navigation .nav-tabs .nav-link:not(.active)" should be "rgb(111, 195, 223)"
    And the computed "border-right-color" of ".secondary-navigation .nav-tabs .nav-link:not(.active)" should be "rgb(111, 195, 223)"
    And the computed "border-bottom-color" of ".secondary-navigation .nav-tabs" should be "rgb(111, 195, 223)"
    And the computed "border-left-color" of ".secondary-navigation .nav-tabs .nav-item + .nav-item .nav-link:not(.active)" should be "rgba(0, 0, 0, 0)"
    And the computed "border-top-color" of ".secondary-navigation .nav-link.active" should be "rgb(243, 133, 24)"
    And the computed "box-shadow" of ".secondary-navigation .nav-link.active" should be "rgb(243, 133, 24) 0px 0px 0px 1px inset"
    And the computed "background-color" of ".secondary-navigation .nav-link.active" should be "rgba(0, 0, 0, 0)"

  Scenario: The Focus ring tile's thickness still applies, dashed
    Given the following "user preferences" exist:
      | user     | preference                | value |
      | student1 | local_accessibility_focus | thick |
    And I log in as "student1"
    When the main region contains core controls
    Then the computed "outline-style" of "#la-test-link:focus-visible" should be "dashed"
    And the computed "outline-width" of "#la-test-link:focus-visible" should be "5px"
