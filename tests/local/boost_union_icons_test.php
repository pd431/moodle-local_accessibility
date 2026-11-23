<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_accessibility\local;

/**
 * Tests for the Boost Union adapter: the icons Boost Union repaints, aimed at the scheme's filters.
 *
 * @package    local_accessibility
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_accessibility\local\boost_union_icons
 */
final class boost_union_icons_test extends \advanced_testcase {
    /** @var string The adapter's selector prefix: under a scheme, three ids' weight. */
    private const PREFIX = 'html[data-a11y-colour]:not(#la-weight) body:not(#la-weight):not(#la-weight) ';

    /** @var array<string, array{purpose: string, name: string}> Modules as core reports them. */
    private const MODULES = [
        'forum' => ['purpose' => 'collaboration', 'name' => 'Forum'],
        'assign' => ['purpose' => 'assessment', 'name' => 'Assignment'],
        'lti' => ['purpose' => 'other', 'name' => 'External "tool" <b>'],
        'label' => ['purpose' => 'other', 'name' => 'Text and media area'],
    ];

    /**
     * Without overrides or tints there is nothing to aim.
     */
    public function test_nothing_set(): void {
        $this->assertSame('', boost_union_icons::rules(self::MODULES, []));
        $same = ['activitypurposeforum' => 'collaboration', 'timelinetintenabled' => 'no'];
        $this->assertSame('', boost_union_icons::rules(self::MODULES, $same), 'the default purpose is no override');
    }

    /**
     * A module moved to another purpose takes that purpose's scheme filter on each of Boost Union's selectors, with
     * !important and three ids' weight.
     */
    public function test_purpose_override(): void {
        $css = boost_union_icons::rules(self::MODULES, ['activitypurposeforum' => 'content']);
        $expected = self::PREFIX . '.activity.modtype_forum .activityiconcontainer.courseicon img,'
            . self::PREFIX . '.modchoosercontainer .modicon_forum.activityiconcontainer img,'
            . self::PREFIX . '#page-header .modicon_forum.activityiconcontainer img,'
            . self::PREFIX . '#page-course-overview #forum_overview_title .activityiconcontainer img'
            . '{filter:url("#la-icon-content") !important}';
        $this->assertSame($expected, $css);
    }

    /**
     * LTI's chooser entry is matched on part of its class, as Boost Union does; a module moved to "other", or to a
     * purpose Boost Union does not know, gets no rule.
     */
    public function test_lti_other_and_unknown(): void {
        $css = boost_union_icons::rules(self::MODULES, ['activitypurposelti' => 'communication']);
        $this->assertStringContainsString(
            self::PREFIX . '.modchoosercontainer [class*="modicon_lti"].activityiconcontainer img',
            $css
        );
        $this->assertStringContainsString('{filter:url("#la-icon-communication") !important}', $css);
        $this->assertSame('', boost_union_icons::rules(self::MODULES, ['activitypurposeforum' => 'other']));
        $this->assertSame('', boost_union_icons::rules(self::MODULES, ['activitypurposeforum' => 'bogus']));
    }

    /**
     * Block tints (Boost Union's Moodle 4.5 line) take each module's purpose, the chosen one where it is moved;
     * modules of purpose "other" stay untinted; a module name in the activities block's title is escaped.
     */
    public function test_tints(): void {
        $config = [
            'timelinetintenabled' => 'yes',
            'upcomingeventstintenabled' => 'yes',
            'recentlyaccesseditemstintenabled' => 'yes',
            'activitiestintenabled' => 'yes',
            'activitypurposeassign' => 'content',
            'activitypurposelti' => 'administration',
        ];
        $css = boost_union_icons::rules(self::MODULES, $config);
        $rules = explode('}', rtrim($css, '}'));
        $this->assertCount(3, $rules, 'forum, assign and lti; not the label');
        $this->assertStringContainsString(
            self::PREFIX . '.block_timeline .theme-boost-union-mod_forum.activityiconcontainer img',
            $rules[0]
        );
        $this->assertStringContainsString(
            self::PREFIX . '.block_calendar_upcoming .theme-boost-union-mod_forum.activityiconcontainer img',
            $rules[0]
        );
        $this->assertStringContainsString(
            self::PREFIX . '.block_recentlyaccesseditems .theme-boost-union-forum.activityiconcontainer img',
            $rules[0]
        );
        $this->assertStringContainsString(self::PREFIX . '.block_activity_modules .content .icon[title="Forum"]', $rules[0]);
        $this->assertStringEndsWith('{filter:url("#la-icon-collaboration") !important', $rules[0]);
        $this->assertStringContainsString('.block_timeline .theme-boost-union-mod_assign.', $rules[1]);
        $this->assertStringEndsWith('{filter:url("#la-icon-content") !important', $rules[1]);
        $this->assertStringContainsString('.icon[title="External \22 tool\22  \3c b\3e "]', $rules[2]);
        $this->assertStringNotContainsString('<b>', $css);
    }

    /**
     * Module names that are not plain identifiers are skipped (they would make a broken selector).
     */
    public function test_odd_module_names(): void {
        $modules = ['bad name' => ['purpose' => 'content', 'name' => 'x']];
        $this->assertSame('', boost_union_icons::rules($modules, ['timelinetintenabled' => 'yes']));
    }

    /**
     * Any theme but Boost Union (or one built on it) gets nothing.
     */
    public function test_other_themes(): void {
        $this->resetAfterTest();
        set_config('activitypurposeforum', 'content', 'theme_boost_union');
        $this->assertSame('', boost_union_icons::css(\theme_config::load('boost')));
    }

    /**
     * With Boost Union installed, its settings are read for the installed modules and the rules are cached per theme
     * and theme revision. Skipped where Boost Union is not installed (as in CI with Boost only).
     */
    public function test_with_boost_union(): void {
        $this->resetAfterTest();
        if (!\core_component::get_component_directory('theme_boost_union')) {
            $this->markTestSkipped('Boost Union is not installed.');
        }
        set_config('activitypurposeforum', 'content', 'theme_boost_union');
        $theme = \theme_config::load('boost_union');
        $css = boost_union_icons::css($theme);
        $this->assertStringContainsString('.activity.modtype_forum .activityiconcontainer.courseicon img', $css);
        $this->assertStringContainsString('{filter:url("#la-icon-content") !important}', $css);
        set_config('activitypurposeforum', 'assessment', 'theme_boost_union');
        $this->assertSame($css, boost_union_icons::css($theme), 'cached per theme and revision');
        // On a site Boost Union's settings reset the theme caches, which moves the revision in the key; PHPUnit forces
        // the revision, so the cache is purged here instead.
        \cache::make('local_accessibility', 'iconoverrides')->purge();
        $this->assertStringContainsString('{filter:url("#la-icon-assessment") !important}', boost_union_icons::css($theme));
    }
}
