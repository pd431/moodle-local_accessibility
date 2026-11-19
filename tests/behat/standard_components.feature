@local @local_accessibility @javascript @la_standard_components
Feature: Standard Moodle components keep their contrast under a colour scheme
  In order to read every page under my colour scheme
  As a user
  I need tables, alerts, menus, link buttons and focus rings to take the scheme's colours

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | Sam       | Student  |

  Scenario Outline: Tables, alerts, menu text and link buttons are readable under <scheme>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    And the page has no Bootstrap 5 custom properties
    When the HTML "<table class='generaltable'><thead><tr><th id='la-th'>Name</th></tr></thead><tbody><tr><td id='la-td1'>One <a id='la-a1' href='#'>link</a></td></tr><tr><td id='la-td2'>Two <a id='la-a2' href='#'>link</a></td></tr></tbody></table>" is added to the main region
    And the HTML "<div class='alert alert-info' id='la-alert'>Notice <a id='la-alert-a' href='#'>link</a></div>" is added to the main region
    And the HTML "<div class='dropdown-menu show' style='position:static'><div class='dropdown-item-text' id='la-menutext'>Plain text</div></div>" is added to the main region
    And the HTML "<button type='button' class='btn btn-link' id='la-linkbtn'>Show more</button>" is added to the main region
    Then the "#la-th" element should have a text contrast of at least 7:1
    And the "#la-td1" element should have a text contrast of at least 7:1
    And the "#la-td2" element should have a text contrast of at least 7:1
    And the "#la-a1" element should have a text contrast of at least 7:1
    And the "#la-a2" element should have a text contrast of at least 7:1
    And the "#la-alert" element should have a text contrast of at least 7:1
    And the "#la-alert-a" element should have a text contrast of at least 7:1
    And the "#la-menutext" element should have a text contrast of at least 7:1
    And the "#la-linkbtn" element should have a text contrast of at least 7:1
    And the computed "text-decoration-line" of "#la-linkbtn" should be "none"

    Examples:
      | scheme       |
      | vscodehc     |
      | highcontrast |
      | yellowblack  |
      | dark         |
      | cream        |
      | blackwhite   |

  Scenario Outline: Keyboard focus shows under <scheme> whatever the theme's focus colour
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    And the main region contains core controls
    And the HTML "<a id='la-focus-a' href='#'>A link in text</a>" is added to the main region
    Then the "#la-focus-a" element should show a keyboard focus indicator
    And the "#la-focus-a" element should have a text contrast of at least 7:1
    And the ".local-accessibility-launcher" element should show a keyboard focus indicator
    And the "#region-main select" element should show a keyboard focus indicator

    Examples:
      | scheme       |
      | vscodehc     |
      | highcontrast |
      | yellowblack  |
      | dark         |
      | cream        |
      | blackwhite   |

  Scenario: Fields and selects have a visible edge under High contrast
    Given the following "user preferences" exist:
      | user     | preference                 | value        |
      | student1 | local_accessibility_colour | highcontrast |
    And I log in as "student1"
    And the page has no Bootstrap 5 custom properties
    And the main region contains core controls
    Then the "#region-main select" element should stand out from its background
    And the "#region-main input[type='text']" element should stand out from its background

  Scenario Outline: Forced link colours, pagination, alerts' edges and image icons follow <scheme>
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    And the page has the extra style "#region-main a:link:not(.btn, h3 a) { color: #0070a8 !important; }"
    When the HTML "<p>Text with <a id='la-forced' href='#'>a forced link</a></p>" is added to the main region
    And the HTML "<ul class='pagination'><li class='page-item'><a class='page-link' id='la-page' href='#'>2</a></li></ul>" is added to the main region
    And the HTML "<span class='badge bg-secondary text-dark' id='la-badge'>Disabled</span>" is added to the main region
    Then the "#la-forced" element should have a text contrast of at least 7:1
    And the "#la-page" element should have a text contrast of at least 7:1
    And the "#la-badge" element should have a text contrast of at least 7:1

    Examples:
      | scheme       |
      | vscodehc     |
      | highcontrast |
      | dark         |

  Scenario Outline: Controls with no side padding get some under <scheme>, and a brand-coloured navbar takes the scheme's surface
    Given the following "user preferences" exist:
      | user     | preference                 | value    |
      | student1 | local_accessibility_colour | <scheme> |
    And I log in as "student1"
    And the page has the extra style "nav.navbar { background-color: #003c3c !important; }"
    When the HTML "<div class='btn dropdown-toggle p-0' role='combobox' id='la-combo' tabindex='0'>Filter by name</div><button type='button' class='btn btn-icon' id='la-iconbtn'><i class='icon fa fa-cog'></i></button>" is added to the main region
    Then the computed "padding-left" of "#la-combo" should not be "0px"
    And the computed "padding-right" of "#la-combo" should not be "0px"
    And the computed "padding-left" of "#la-iconbtn" should be "0px"
    And the computed "background-color" of "nav.navbar" should not be "rgb(0, 60, 60)"

    Examples:
      | scheme     |
      | blackwhite |
      | cream      |

  Scenario: With no scheme a combobox toggle keeps core's padding
    Given I log in as "student1"
    When the HTML "<div class='btn dropdown-toggle p-0' role='combobox' id='la-combo' tabindex='0'>Filter by name</div>" is added to the main region
    Then the computed "padding-left" of "#la-combo" should be "0px"
