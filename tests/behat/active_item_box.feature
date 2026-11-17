@local @local_accessibility @javascript @la_activebox
Feature: The current navigation item is shown by its fill, with readable text and no box
  In order to see which page, tab or activity I am on without a box around a link
  As a user of a colour scheme
  I need the current item in the top nav, the course tabs and the course index on a fill of its own, with its text at
  4.5:1 or more on that fill, and keyboard focus still shown on it

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

  Scenario Outline: The top nav, course tabs and course index show the current item without a box under <scheme>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    Then the computed "box-shadow" of ".primary-navigation [aria-current]" should be "none"
    And the ".primary-navigation [aria-current]" element should have a text contrast of at least 4.5:1
    When I am on the "C1" course page
    Then the computed "box-shadow" of ".secondary-navigation :is(.nav-link.active, .mds-nav-pill--selected)" should be "none"
    And the ".secondary-navigation :is(.nav-link.active, .mds-nav-pill--selected)" element should have a text contrast of at least 4.5:1
    When I am on the "Page 1" "page activity" page
    Then the computed "box-shadow" of ".courseindex-item.pageitem" should be "none"
    And the ".courseindex-item.pageitem .courseindex-link" element should have a text contrast of at least 4.5:1

    Examples:
      | scheme       |
      | highcontrast |
      | yellowblack  |
      | cream        |
      | vscodehc     |

  Scenario Outline: Keyboard focus on the current item is still shown under <scheme>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    Then the ".primary-navigation [aria-current]" element should show a keyboard focus indicator

    Examples:
      | scheme       |
      | highcontrast |
      | vscodehc     |
