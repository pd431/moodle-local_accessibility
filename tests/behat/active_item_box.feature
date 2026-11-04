@local @local_accessibility @javascript @la_activebox
Feature: The current navigation item is boxed in the scheme's active colour
  In order to see at a glance which page, tab or activity I am on
  As a user of the Dark high contrast (VS Code) scheme
  I need the current item in the top nav, the course tabs and the course index boxed in orange

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

  Scenario: The scheme declares the active token and the top nav, course tabs and course index show the box
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I log in as "student1"
    Then the page root should have attribute "data-a11y-tokens" with value "border focus selection active"
    And the computed "box-shadow" of ".primary-navigation .nav-link[aria-current]" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"
    When I am on the "C1" course page
    Then the computed "box-shadow" of ".secondary-navigation .nav-link.active" should be "rgb(243, 133, 24) 0px 0px 0px 1px inset"
    And the computed "border-top-color" of ".secondary-navigation .nav-link.active" should be "rgb(243, 133, 24)"
    When I am on the "Page 1" "page activity" page
    Then the computed "box-shadow" of ".courseindex-item.pageitem" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"

  Scenario: Keyboard focus on the current item shows both the box and the focus ring
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I log in as "student1"
    Then the ".primary-navigation .nav-link[aria-current]" element should show a keyboard focus indicator
    And the computed "outline-color" of ".primary-navigation .nav-link[aria-current]:focus-visible" should be "rgb(243, 133, 24)"
    And the computed "box-shadow" of ".primary-navigation .nav-link[aria-current]:focus-visible" should be "rgb(243, 133, 24) 0px 0px 0px 2px inset"

  Scenario: Other schemes declare no active colour and paint as before
    Given the following "user preferences" exist:
      | user     | preference                 | value        |
      | student1 | local_accessibility_colour | highcontrast |
    And I log in as "student1"
    Then the page root should not have attribute "data-a11y-tokens"
    And the computed "box-shadow" of ".primary-navigation .nav-link[aria-current]" should be "none"
