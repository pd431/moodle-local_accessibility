@local @local_accessibility @javascript @la_activityicons
Feature: Activity icon colours reach APCA Lc 75 and 4.5:1 under a colour scheme
  In order to tell activity types apart on a dark or light scheme
  As a user with a colour scheme active
  I need every activity icon colour lifted or lowered until it reaches APCA Lc 75 and WCAG 2 4.5:1 against what it sits on

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
      | activity | course | name         |
      | assign   | C1     | Assignment 1 |
      | forum    | C1     | Forum 1      |
      | page     | C1     | Page 1       |

  Scenario Outline: Every icon reaches Lc 75 and 4.5:1 under <colour>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <colour> |
    And I am on the "C1" course page logged in as "student1"
    Then the activity icon for "Assignment 1" should use the filter "la-icon-assessment"
    And the activity icon for "Forum 1" should use the filter "la-icon-collaboration"
    And the activity icon for "Page 1" should use the filter "la-icon-content"
    And the activity icon for "Assignment 1" should have an APCA contrast of at least 75
    And the activity icon for "Forum 1" should have an APCA contrast of at least 75
    And the activity icon for "Page 1" should have an APCA contrast of at least 75
    And the activity icon for "Assignment 1" should have a contrast ratio of at least 4.5
    And the activity icon for "Forum 1" should have a contrast ratio of at least 4.5
    And the activity icon for "Page 1" should have a contrast ratio of at least 4.5

    Examples:
      | colour       |
      | vscodehc     |
      | highcontrast |
      | cream        |
      | dark         |

  Scenario: Branded and unfiltered icons are left alone
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I am on the "C1" course page logged in as "student1"
    When the HTML "<div class='activityiconcontainer content isbranded'><img id='t-brand' class='activityicon' src='/pix/moodlelogo.png'></div><div class='activityiconcontainer content'><img id='t-nofilter' class='activityicon nofilter' src='/pix/moodlelogo.png'></div>" is added to the main region
    Then the computed "filter" of "#t-brand" should be "none"
    And the computed "filter" of "#t-nofilter" should be "none"

  Scenario: Without a scheme Boost's own filters apply
    Given I am on the "C1" course page logged in as "student1"
    Then the page root should not have attribute "data-a11y-colour"
    And the activity icon for "Page 1" should not use the filter "la-icon-content"

  Scenario: The Images view still shows activity icons
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
      | student1 | local_accessibility_images | hide     |
    And I am on the "C1" course page logged in as "student1"
    Then ".activityiconcontainer.content .activityicon" "css_element" should be visible
    And the activity icon for "Page 1" should have an APCA contrast of at least 75
    And the activity icon for "Page 1" should have a contrast ratio of at least 4.5
