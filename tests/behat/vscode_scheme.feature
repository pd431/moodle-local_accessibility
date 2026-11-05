@local @local_accessibility @javascript
Feature: VS Code dark high contrast colour scheme
  In order to read Moodle the way I read my editor
  As a user who relies on high contrast with visible edges
  I need a scheme with black surfaces, cyan borders, an orange focus ring and yellow selection

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | Student   | One      |

  Scenario: The scheme is offered as a swatch and applies the VS Code palette
    Given I log in as "student1"
    When I click on "Accessibility settings" "button"
    And I click on "Colours, Site default" "button"
    And I click on "[data-view='colour'] [data-scheme='vscodehc']" "css_element"
    And I wait until the page is ready
    Then the page root should have attribute "data-a11y-colour" with value "vscodehc"
    And the page root should have attribute "data-bs-theme" with value "dark"
    And the page root should not have attribute "data-a11y-lowcontrast"
    And the computed "background-color" of "body" should be "rgb(0, 0, 0)"
    And the computed "color" of "body" should be "rgb(255, 255, 255)"
    And I click on "Accessibility settings" "button"
    And "Colours, Dark high contrast (VS Code)" "button" should exist

  Scenario: Surfaces are black and told apart by a cyan border
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I log in as "student1"
    When the main region contains core controls
    And the HTML "<div id='la-card' class='card'><div class='card-body'>Card</div></div><button id='la-btn' class='btn btn-secondary'>Secondary</button>" is added to the main region
    Then the page root should have attribute "data-a11y-tokens" with value "border focus selection active"
    And the computed "background-color" of "#la-card" should be "rgb(0, 0, 0)"
    And the computed "border-top-color" of "#la-card" should be "rgb(111, 195, 223)"
    And the computed "border-top-color" of "#la-test-text" should be "rgb(111, 195, 223)"
    And the computed "border-top-color" of "#la-btn" should be "rgb(111, 195, 223)"
    And the "#la-card" element should stand out from its background
    And the "#la-test-text" element should stand out from its background

  Scenario: Plain buttons and the notification toggle take the scheme
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I am on the "C1" course page logged in as "student1"
    And the HTML "<button id='la-plain' class='btn btn-sm'>Plain</button>" is added to the main region
    Then the computed "color" of "#la-plain" should be "rgb(255, 255, 255)"
    And the computed "background-color" of "#la-plain" should be "rgb(12, 20, 31)"
    And the computed "border-top-color" of "#la-plain" should be "rgb(111, 195, 223)"
    And the computed "color" of ".popover-region-toggle" should be "rgb(255, 255, 255)"
    And the computed "color" of "#theme_boost-drawers-courseindex .drawertoggle" should be "rgb(255, 255, 255)"

  Scenario: Boxed activities have no separator line and a badge has an edge
    Given the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | course | name   | section | completion | completionview |
      | page     | C1     | Page 1 | 1       | 2          | 1              |
      | page     | C1     | Page 2 | 1       | 2          | 1              |
    And the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I am on the "C1" course page logged in as "student1"
    Then the computed "border-top-width" of ".section .activity:nth-child(2)" should be "0px"
    And the computed "border-top-width" of ".section .activity:nth-child(2) .activity-item" should be "1px"
    And the computed "border-top-color" of ".section .activity:nth-child(2) .activity-item" should be "rgb(111, 195, 223)"
    When I am on the "Page 1" "page activity" page
    Then the computed "border-top-width" of ".activity-header .badge" should be "1px"
    And the computed "border-top-color" of ".activity-header .badge" should be "rgb(111, 195, 223)"

  Scenario: Links are VS Code blue and pass 7:1
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I log in as "student1"
    When the main region contains core controls
    Then the computed "color" of "#la-test-link" should be "rgb(139, 202, 255)"
    And the "#la-test-link" element should have a text contrast of at least 7:1
    And the "#la-test-item" element should have a text contrast of at least 7:1

  Scenario: Keyboard focus shows the orange focus ring whatever the Focus ring tile says
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I log in as "student1"
    When the main region contains core controls
    Then the "#la-test-link" element should show a keyboard focus indicator
    And the computed "outline-color" of "#la-test-link:focus-visible" should be "rgb(243, 133, 24)"

  Scenario: The Focus ring tile keeps its thickness and takes the scheme's colour
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
      | student1 | local_accessibility_focus  | thick    |
    And I log in as "student1"
    When the main region contains core controls
    Then the "#la-test-link" element should show a keyboard focus indicator
    And the computed "outline-color" of "#la-test-link:focus-visible" should be "rgb(243, 133, 24)"
    And the computed "outline-width" of "#la-test-link:focus-visible" should be "5px"

  Scenario: Selected text is yellow on black
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | vscodehc |
    And I log in as "student1"
    When the main region contains core controls
    Then the computed "background-color" of "#la-test-item::selection" should be "rgb(243, 245, 24)"
    And the computed "color" of "#la-test-item::selection" should be "rgb(0, 0, 0)"

  Scenario: The other schemes paint exactly as before
    Given the following "user preferences" exist:
      | user     | preference                 | value       |
      | student1 | local_accessibility_colour | yellowblack |
    And I log in as "student1"
    When the main region contains core controls
    Then the page root should not have attribute "data-a11y-tokens"
    And the computed "border-top-color" of "#la-test-text" should not be "rgb(111, 195, 223)"
    And the computed "background-color" of "#la-test-item::selection" should not be "rgb(243, 245, 24)"

  Scenario: An administrator can make the scheme the site default and lock it
    Given the following config values are set as admin:
      | default_colour | vscodehc | local_accessibility |
      | lock_colour    | 1        | local_accessibility |
    When I log in as "student1"
    Then the page root should have attribute "data-a11y-colour" with value "vscodehc"
    And I click on "Accessibility settings" "button"
    And the "aria-disabled" attribute of "Colours, Dark high contrast (VS Code)" "button" should contain "true"
