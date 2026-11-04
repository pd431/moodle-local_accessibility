@local @local_accessibility @javascript @la_linkpurpose
Feature: Links are coloured by purpose under a colour scheme
  In order to tell real links from navigation and controls
  As a user with a colour scheme active
  I need content links in the link colour and control links in the text colour

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
      | activity | course | name   | intro                                   |
      | page     | C1     | Page 1 | <p>See <a href="#">the notes</a> now</p> |

  Scenario: Activity names and navigation take the text colour
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I am on the "C1" course page logged in as "student1"
    Then the computed "color" of ".activity-item .activityname a" should be "rgb(255, 255, 255)"
    And the computed "text-decoration-line" of ".activity-item .activityname a" should be "none"
    And the computed "color" of ".primary-navigation .nav-link" should be "rgb(255, 255, 255)"
    And the computed "color" of ".secondary-navigation .nav-link" should be "rgb(255, 255, 255)"
    And the computed "color" of ".courseindex-link" should be "rgb(255, 255, 255)"
    And the computed "text-decoration-line" of ".courseindex-link" should be "none"
    And the computed "color" of ".sectionname a" should be "rgb(255, 255, 255)"
    When I click on "User menu" "button"
    Then the computed "color" of ".usermenu .dropdown-item" should be "rgb(255, 255, 255)"

  Scenario: Prose links keep the link colour on every page, including an activity and admin settings
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I am on the "Page 1" "page activity" page logged in as "student1"
    Then the computed "color" of "#region-main .no-overflow a" should be "rgb(139, 202, 255)"
    And the computed "text-decoration-line" of "#region-main .no-overflow a" should be "underline"
    And the "#region-main .no-overflow a" element should have a text contrast of at least 7:1

  Scenario: Block links, badges, sort links and pagination take the text colour; table cells stay links
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I log in as "student1"
    When the main region contains core controls
    And the HTML "<h3><a id='t-h' href='#'>Title</a></h3><a id='t-card' href='#'><h5>Card</h5></a><span class='badge'><a id='t-b' href='#'>Badge</a></span><table><thead><tr><th><a id='t-th' href='#'>Sort</a></th></tr></thead><tbody><tr><td><a id='t-td' href='#'>Cell</a></td></tr></tbody></table>" is added to the main region
    Then the computed "color" of "#t-h" should be "rgb(255, 255, 255)"
    And the computed "color" of "#t-card" should be "rgb(255, 255, 255)"
    And the computed "color" of "#t-b" should be "rgb(255, 255, 255)"
    And the computed "color" of "#t-th" should be "rgb(255, 255, 255)"
    And the computed "color" of "#t-td" should be "rgb(139, 202, 255)"
    And the computed "color" of "#la-test-controls .page-item:not(.active) .page-link" should be "rgb(255, 255, 255)"
    And the computed "color" of "#la-test-link" should be "rgb(139, 202, 255)"
    And the "#la-test-page" element should have a text contrast of at least 4.5:1

  Scenario Outline: The split holds and stays readable under <colour>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <colour> |
    And I am on the "Page 1" "page activity" page logged in as "student1"
    Then the "#region-main .no-overflow a" element should have a text contrast of at least 7:1
    And the ".primary-navigation .nav-link" element should have a text contrast of at least 7:1
    And the ".courseindex-link" element should have a text contrast of at least 7:1
    And the "#region-main .no-overflow a" element should look different from the ".courseindex-link" element

    Examples:
      | colour       |
      | highcontrast |
      | blackwhite   |
      | cream        |
      | dark         |

  Scenario: Without a scheme the theme paints its own links
    Given I am on the "C1" course page logged in as "student1"
    Then the page root should not have attribute "data-a11y-colour"
    And the computed "color" of ".activity-item .activityname a" should not be "rgb(255, 255, 255)"
