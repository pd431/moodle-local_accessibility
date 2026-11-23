@local @local_accessibility @javascript @la_iconbaseline
Feature: Activity icons under a colour scheme start from the colours the theme paints
  In order to recognise activities by the colours my site gives them
  As a user with a colour scheme active
  I need each activity icon to keep the hue the theme paints it in, lifted or lowered until it is readable

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
      | activity | course | name         | duedate         |
      | assign   | C1     | Assignment 1 | ##+3 days##     |
      | forum    | C1     | Forum 1      | 0               |
      | page     | C1     | Page 1       | 0               |
    And the following "user preferences" exist:
      | user     | preference                 | value |
      | student1 | local_accessibility_colour | cream |

  Scenario: Under Cream the content icon keeps the hue Boost paints it in
    # styles.php compiles the theme after sending a fallback sheet; compile it now so the first page reads it.
    Given the site theme's stylesheet is compiled
    When I am on the "C1" course page logged in as "student1"
    Then the scheme's "content" icon colour should keep the hue of "#0099ad"
    And the activity icon for "Page 1" should have an APCA contrast of at least 75
    And the activity icon for "Page 1" should have a contrast ratio of at least 4.5

  Scenario: Under Cream the content icon keeps the hue of Boost Union's activity icon colour setting
    Given the "boost_union" theme is installed
    And the following config values are set as admin:
      | theme                    | boost_union |                   |
      | activityiconcolorcontent | #2e7d32     | theme_boost_union |
    And the site theme's stylesheet is compiled
    When I am on the "C1" course page logged in as "student1"
    Then the scheme's "content" icon colour should keep the hue of "#2e7d32"
    And the activity icon for "Page 1" should have an APCA contrast of at least 75
    And the activity icon for "Page 1" should have a contrast ratio of at least 4.5

  Scenario: Under Cream the content icon keeps the hue a Raw initial SCSS colour paints, where Boost paints from colours
    Given the "boost_union" theme is installed
    And Boost paints activity icons from colours
    And the following config values are set as admin:
      | theme   | boost_union                       |                   |
      | scsspre | $activity-icon-content-bg: #6a1b9a; | theme_boost_union |
    And the site theme's stylesheet is compiled
    When I am on the "C1" course page logged in as "student1"
    Then the scheme's "content" icon colour should keep the hue of "#6a1b9a"
    And the activity icon for "Page 1" should have a contrast ratio of at least 4.5

  Scenario: Where Boost paints from filter chains, a Raw initial SCSS background colour paints nothing and the baseline stays
    Given the "boost_union" theme is installed
    And Boost paints activity icons from filter chains
    And the following config values are set as admin:
      | theme   | boost_union                       |                   |
      | scsspre | $activity-icon-content-bg: #6a1b9a; | theme_boost_union |
    And the site theme's stylesheet is compiled
    When I am on the "C1" course page logged in as "student1"
    Then the scheme's "content" icon colour should keep the hue of "#0099ad"

  Scenario: Under Cream the content icon keeps the hue Classic paints it in
    Given the "classic" theme is installed
    And the following config values are set as admin:
      | theme | classic |
    And the site theme's stylesheet is compiled
    When I am on the "C1" course page logged in as "student1"
    Then the scheme's "content" icon colour should keep the hue of "#0099ad"
    And the activity icon for "Page 1" should have a contrast ratio of at least 4.5

  Scenario: Under High contrast a forum Boost Union shows as content takes the scheme's content colour
    Given the "boost_union" theme is installed
    And the following "user preferences" exist:
      | user     | preference                 | value        |
      | student1 | local_accessibility_colour | highcontrast |
    And the following config values are set as admin:
      | theme                | boost_union |                   |
      | activitypurposeforum | content     | theme_boost_union |
    When I am on the "C1" course page logged in as "student1"
    Then the activity icon for "Forum 1" should use the filter "la-icon-content"
    And the activity icon for "Forum 1" should have an APCA contrast of at least 75
    And the activity icon for "Forum 1" should have a contrast ratio of at least 4.5
    And the activity icon for "Assignment 1" should use the filter "la-icon-assessment"

  Scenario: Under High contrast an icon Boost Union tints in the timeline takes the scheme's colour for its purpose
    Given the "boost_union" theme is installed
    And Boost Union has the "timelinetintenabled" setting
    And the following "user preferences" exist:
      | user     | preference                 | value        |
      | student1 | local_accessibility_colour | highcontrast |
    And the following config values are set as admin:
      | theme                 | boost_union |                   |
      | timelinetintenabled   | yes         | theme_boost_union |
      | activitypurposeassign | content     | theme_boost_union |
    When I log in as "student1"
    And I wait until ".block_timeline .theme-boost-union-mod_assign.activityiconcontainer img" "css_element" exists
    Then the ".block_timeline .theme-boost-union-mod_assign.activityiconcontainer img" element should use the filter "la-icon-content"

  Scenario: Without a scheme Boost Union's own purpose override paints the icon
    Given the "boost_union" theme is installed
    And the following "user preferences" exist:
      | user     | preference                 | value   |
      | student1 | local_accessibility_colour | default |
    And the following config values are set as admin:
      | theme                | boost_union |                   |
      | activitypurposeforum | content     | theme_boost_union |
    When I am on the "C1" course page logged in as "student1"
    Then the page root should not have attribute "data-a11y-colour"
    And the activity icon for "Forum 1" should not use the filter "la-icon-content"
    And the activity icon for "Forum 1" should not use the filter "la-icon-collaboration"
